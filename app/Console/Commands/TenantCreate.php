<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\TenantManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TenantCreate extends Command
{
    protected $signature = 'tenant:create
        {--subdomain= : Subdomain (misal: mansipera)}
        {--name=      : Nama institusi / klien}
        {--email=     : Email kontak}
        {--phone=     : No. WhatsApp (628xxx)}
        {--plan=trial : Paket: trial|basic|pro|lifetime}
        {--admin-name=  : Nama admin default}
        {--admin-email= : Email admin default}
        {--force      : Skip konfirmasi}';

    protected $description = 'Buat tenant baru lengkap (DB + migrasi + admin) via CLI';

    public function handle(TenantManager $manager): int
    {
        $subdomain  = $this->option('subdomain') ?: $this->ask('Subdomain (tanpa .apji.org)');
        $name       = $this->option('name')      ?: $this->ask('Nama institusi / klien');
        $plan       = $this->option('plan')      ?: $this->choice('Paket', ['trial', 'basic', 'pro', 'lifetime'], 0);

        $subdomain = strtolower(preg_replace('/[^a-z0-9\-]/', '', $subdomain));

        // Cek apakah sudah ada
        $existing = Tenant::where('subdomain', $subdomain)->first();
        if ($existing) {
            $this->warn("Tenant '{$subdomain}' sudah ada (ID: {$existing->id}, status: {$existing->status}).");
            if (!$this->option('force') && !$this->confirm('Lanjutkan setup DB + migrasi untuk tenant ini?')) {
                return 0;
            }
            return $this->setupExisting($existing, $manager);
        }

        $data = [
            'name'        => $name,
            'subdomain'   => $subdomain,
            'email'       => $this->option('email'),
            'phone'       => $this->option('phone'),
            'plan'        => $plan,
            'admin_name'  => $this->option('admin-name'),
            'admin_email' => $this->option('admin-email'),
        ];

        $planDuration = config("tenants.plans.{$plan}.duration");
        $data['status']        = $plan === 'trial' ? 'trial' : 'active';
        $data['trial_ends_at'] = $plan === 'trial' && $planDuration ? now()->addDays($planDuration) : null;
        $data['expires_at']    = ($plan !== 'trial' && $planDuration) ? now()->addDays($planDuration) : null;

        $this->info("Membuat tenant: {$name} ({$subdomain}.apji.org)");
        $this->newLine();

        $result = $manager->createWithSteps($data);

        foreach ($result['steps'] ?? [] as $key => $step) {
            $icon = $step['ok'] ? '<info>✓</info>' : '<error>✗</error>';
            $this->line("  {$icon} [{$key}] " . ($step['msg'] ?? ''));
        }

        $this->newLine();
        if ($result['success']) {
            $this->info("✓ Tenant berhasil dibuat!");
            $this->line("  URL: <href=https://{$subdomain}.apji.org>https://{$subdomain}.apji.org</>");
            $this->line("  Admin panel: https://portal.apji.org/admin/tenants/{$result['tenant_id']}");
        } else {
            $this->error("✗ Pembuatan tenant tidak selesai. Lihat langkah yang gagal di atas.");
            if (!empty($result['tenant_id'])) {
                $this->line("  Lanjutkan dari: https://portal.apji.org/admin/tenants/{$result['tenant_id']}");
            }
            return 1;
        }

        return 0;
    }

    private function setupExisting(Tenant $tenant, TenantManager $manager): int
    {
        $this->info("Setup DB + migrasi untuk tenant yang sudah ada...");

        // Buat DB jika belum ada
        $this->line("  → Membuat database {$tenant->db_name}...");
        try {
            $this->callCreateDatabase($tenant);
            $this->info("  ✓ Database OK");
        } catch (\Throwable $e) {
            $this->error("  ✗ Gagal buat DB: " . $e->getMessage());
            return 1;
        }

        // Migrasi
        $this->line("  → Migrasi...");
        $result = $manager->migrate($tenant);
        if ($result['success']) {
            $this->info("  ✓ Migrasi berhasil");
        } else {
            $this->error("  ✗ Migrasi gagal: " . $result['output']);
            return 1;
        }

        // Seed admin
        if ($tenant->admin_email) {
            $this->line("  → Membuat akun admin...");
            $creds = $manager->seedAdminUser($tenant);
            if ($creds) {
                $this->info("  ✓ Admin: {$creds['email']} | Password: {$creds['password']}");
            }
        }

        $this->newLine();
        $this->info("✓ Selesai! https://{$tenant->subdomain}.apji.org");
        return 0;
    }

    private function callCreateDatabase(Tenant $tenant): void
    {
        $charset   = config('database.connections.mysql.charset', 'utf8mb4');
        $collation = config('database.connections.mysql.collation', 'utf8mb4_unicode_ci');
        $dbName    = $tenant->db_name;
        $appUser   = config('database.connections.mysql.username');
        $appHost   = config('database.connections.mysql.host', '127.0.0.1');

        $sql     = "CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET {$charset} COLLATE {$collation}";
        $useAdmin = !empty(env('DB_ADMIN_USERNAME'));

        if ($useAdmin) {
            $conn = DB::connection('mysql_admin');
            $conn->statement($sql);
            try {
                $conn->statement("GRANT ALL PRIVILEGES ON `{$dbName}`.* TO '{$appUser}'@'{$appHost}'");
                $conn->statement("FLUSH PRIVILEGES");
            } catch (\Throwable) {}
        } else {
            DB::statement($sql);
        }
    }
}
