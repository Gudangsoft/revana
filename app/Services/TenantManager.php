<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantManager
{
    /**
     * Buat tenant baru: simpan ke DB master, buat database, jalankan migration + seed.
     */
    public function create(array $data): Tenant
    {
        // Normalisasi subdomain
        $data['subdomain'] = strtolower(preg_replace('/[^a-z0-9\-]/', '', $data['subdomain']));
        $data['db_name']   = 'tenant_' . str_replace('-', '_', $data['subdomain']);

        $tenant = Tenant::create($data);

        // Inisialisasi fitur dari plan
        $tenant->initFeaturesFromPlan();

        // Buat database dan jalankan migration
        $this->createDatabase($tenant);
        $this->migrate($tenant);

        Log::info("Tenant created: {$tenant->subdomain}", ['db' => $tenant->db_name]);

        return $tenant;
    }

    /**
     * Hapus tenant: drop database dan hapus record.
     */
    public function delete(Tenant $tenant): void
    {
        $dbName = $tenant->db_name;

        try {
            DB::statement("DROP DATABASE IF EXISTS `{$dbName}`");
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
     * Jalankan migration ke semua tenant aktif.
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
     * Ambil statistik dari database tenant (jumlah user, artikel, dll).
     */
    public function stats(Tenant $tenant): array
    {
        try {
            $this->switchToTenant($tenant);

            $stats = [
                'users'      => DB::table('users')->count(),
                'pics'       => DB::table('pics')->count(),
                'journals'   => DB::table('journals')->exists() ? DB::table('journals')->count() : 0,
                'articles'   => DB::table('submissions')->exists() ? DB::table('submissions')->count() : 0,
                'last_login' => null,
            ];

            // Coba ambil last login dari tabel users
            if (DB::getSchemaBuilder()->hasColumn('users', 'last_login_at')) {
                $stats['last_login'] = DB::table('users')->max('last_login_at');
            }

            $this->switchToMaster();
            return $stats;
        } catch (\Exception $e) {
            $this->switchToMaster();
            Log::warning("Stats tenant {$tenant->subdomain} gagal: {$e->getMessage()}");
            return ['error' => $e->getMessage()];
        }
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function createDatabase(Tenant $tenant): void
    {
        $dbName = $tenant->db_name;
        $charset   = config('database.connections.mysql.charset', 'utf8mb4');
        $collation = config('database.connections.mysql.collation', 'utf8mb4_unicode_ci');

        DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET {$charset} COLLATE {$collation}");
        Log::info("Tenant DB created: {$dbName}");
    }

    private function switchToTenant(Tenant $tenant): void
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

    private function switchToMaster(): void
    {
        Config::set('database.default', 'mysql');
        DB::purge('tenant');
    }
}
