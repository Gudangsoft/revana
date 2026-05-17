<?php

namespace App\Services;

use App\Models\Tenant;
use App\Services\FonnteService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TenantManager
{
    /**
     * Buat tenant baru: simpan ke DB master, buat database, jalankan migration + seed admin.
     */
    public function create(array $data): Tenant
    {
        $data['subdomain'] = strtolower(preg_replace('/[^a-z0-9\-]/', '', $data['subdomain']));
        $data['db_name']   = 'tenant_' . str_replace('-', '_', $data['subdomain']);

        $tenant = Tenant::create($data);
        $tenant->initFeaturesFromPlan();

        $this->createDatabase($tenant);
        $this->migrate($tenant);

        // Buat akun admin default & kirim kredensial
        $credentials = $this->seedAdminUser($tenant);
        if ($credentials && $tenant->phone) {
            $this->sendWelcomeWa($tenant, $credentials);
        }

        Log::info("Tenant created: {$tenant->subdomain}", ['db' => $tenant->db_name]);

        return $tenant->refresh();
    }

    /**
     * Buat akun admin default di database tenant.
     * Kembalikan array ['email', 'password'] atau null jika tidak ada email admin.
     */
    public function seedAdminUser(Tenant $tenant): ?array
    {
        if (!$tenant->admin_email) return null;

        $password = Str::random(10);

        try {
            $this->switchToTenant($tenant);

            // Cek apakah sudah ada user dengan email ini
            $exists = DB::table('users')->where('email', $tenant->admin_email)->exists();
            if (!$exists) {
                DB::table('users')->insert([
                    'name'       => $tenant->admin_name ?: 'Admin',
                    'email'      => $tenant->admin_email,
                    'password'   => Hash::make($password),
                    'role'       => 'admin',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->switchToMaster();
            return ['email' => $tenant->admin_email, 'password' => $password];
        } catch (\Exception $e) {
            $this->switchToMaster();
            Log::error("seedAdminUser gagal untuk {$tenant->subdomain}: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Kirim kredensial + info akses ke nomor WA admin tenant.
     */
    public function sendWelcomeWa(Tenant $tenant, array $credentials): void
    {
        try {
            $url    = 'https://' . $tenant->subdomain . '.' . $this->baseDomain();
            $name   = $tenant->admin_name ?: $tenant->name;

            $message = "Halo *{$name}*,\n\n"
                . "Sistem SIPERA untuk *{$tenant->name}* telah siap digunakan.\n\n"
                . "🌐 *Akses Sistem*\n{$url}\n\n"
                . "🔐 *Kredensial Admin*\n"
                . "Email: {$credentials['email']}\n"
                . "Password: {$credentials['password']}\n\n"
                . "⚠️ Segera ganti password setelah login pertama.\n\n"
                . "Hubungi kami jika butuh bantuan.\n"
                . "Tim APJI";

            (new FonnteService())->send($tenant->phone, $message);
        } catch (\Exception $e) {
            Log::warning("sendWelcomeWa gagal untuk {$tenant->subdomain}: {$e->getMessage()}");
        }
    }

    /**
     * Hapus tenant: drop database dan hapus record.
     */
    public function delete(Tenant $tenant): void
    {
        $dbName = $tenant->db_name;
        try {
            DB::connection('mysql_admin')->statement("DROP DATABASE IF EXISTS `{$dbName}`");
            Log::info("Tenant DB dropped: {$dbName}");
        } catch (\Exception $e) {
            Log::error("Gagal drop DB tenant: {$e->getMessage()}");
        }
        $tenant->delete();
    }

    /**
     * Jalankan migration ke database tenant tertentu.
     */
    public function migrate(Tenant $tenant): array
    {
        $this->switchToTenant($tenant);
        try {
            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--force'    => true,
                '--path'     => 'database/migrations',
            ]);
            $output = Artisan::output();
            $this->switchToMaster();
            return ['success' => true, 'output' => $output];
        } catch (\Exception $e) {
            $this->switchToMaster();
            Log::error("Migrate tenant {$tenant->subdomain} gagal: {$e->getMessage()}");
            return ['success' => false, 'output' => $e->getMessage()];
        }
    }

    /**
     * Jalankan migration ke semua tenant.
     */
    public function migrateAll(): array
    {
        $results = [];
        foreach (Tenant::all() as $tenant) {
            $results[$tenant->subdomain] = $this->migrate($tenant);
        }
        return $results;
    }

    /**
     * Ambil statistik dari database tenant.
     */
    public function stats(Tenant $tenant): array
    {
        try {
            $this->switchToTenant($tenant);

            $stats = [
                'users'      => DB::table('users')->count(),
                'pics'       => DB::table('pics')->count(),
                'journals'   => DB::getSchemaBuilder()->hasTable('journals') ? DB::table('journals')->count() : 0,
                'articles'   => DB::getSchemaBuilder()->hasTable('submissions') ? DB::table('submissions')->count() : 0,
                'last_login' => null,
                'db_ok'      => true,
            ];

            if (DB::getSchemaBuilder()->hasColumn('users', 'last_login_at')) {
                $stats['last_login'] = DB::table('users')->max('last_login_at');
            }

            $this->switchToMaster();
            return $stats;
        } catch (\Exception $e) {
            $this->switchToMaster();
            Log::warning("Stats tenant {$tenant->subdomain} gagal: {$e->getMessage()}");
            return ['error' => $e->getMessage(), 'db_ok' => false];
        }
    }

    /**
     * Perpanjang masa aktif tenant.
     */
    public function renew(Tenant $tenant, int $days): void
    {
        $base = $tenant->expires_at && $tenant->expires_at->isFuture()
            ? $tenant->expires_at
            : now();

        $tenant->update([
            'expires_at' => $base->addDays($days),
            'status'     => 'active',
        ]);

        Log::info("Tenant {$tenant->subdomain} diperpanjang {$days} hari.");
    }

    /**
     * Ubah paket tenant + sinkronisasi fitur default plan baru.
     */
    public function changePlan(Tenant $tenant, string $plan): void
    {
        $tenant->update(['plan' => $plan]);
        $tenant->initFeaturesFromPlan();

        Log::info("Tenant {$tenant->subdomain} plan diubah ke {$plan}.");
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function createDatabase(Tenant $tenant): void
    {
        $charset   = config('database.connections.mysql.charset', 'utf8mb4');
        $collation = config('database.connections.mysql.collation', 'utf8mb4_unicode_ci');
        // Gunakan koneksi mysql_admin (user dengan privilege CREATE DATABASE)
        DB::connection('mysql_admin')->statement(
            "CREATE DATABASE IF NOT EXISTS `{$tenant->db_name}` CHARACTER SET {$charset} COLLATE {$collation}"
        );
        Log::info("Tenant DB created: {$tenant->db_name}");
    }

    public function switchToTenant(Tenant $tenant): void
    {
        $connection = config('database.connections.mysql');
        Config::set('database.connections.tenant', array_merge($connection, [
            'database' => $tenant->db_name,
            'username' => $tenant->db_user ?: $connection['username'],
            'password' => $tenant->db_password ?: $connection['password'],
        ]));
        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    public function switchToMaster(): void
    {
        Config::set('database.default', 'mysql');
        DB::purge('tenant');
    }

    private function baseDomain(): string
    {
        $master = config('tenants.master_domain', 'portal.apji.org');
        $parts  = explode('.', $master);
        array_shift($parts);
        return implode('.', $parts);
    }
}
