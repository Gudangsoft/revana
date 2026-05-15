<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\TenantManager;
use Illuminate\Console\Command;

class TenantsMigrate extends Command
{
    protected $signature = 'tenants:migrate
                            {--tenant= : Subdomain tenant tertentu (kosong = semua)}
                            {--fresh : Drop semua tabel lalu migrate ulang (hati-hati!)}';

    protected $description = 'Jalankan migration ke database semua tenant (atau tenant tertentu)';

    public function handle(TenantManager $manager): int
    {
        $subdomain = $this->option('tenant');
        $fresh     = $this->option('fresh');

        if ($subdomain) {
            $tenant = Tenant::where('subdomain', $subdomain)->first();
            if (!$tenant) {
                $this->error("Tenant '{$subdomain}' tidak ditemukan.");
                return Command::FAILURE;
            }
            $tenants = collect([$tenant]);
        } else {
            $tenants = Tenant::all();
        }

        if ($tenants->isEmpty()) {
            $this->warn('Tidak ada tenant yang terdaftar.');
            return Command::SUCCESS;
        }

        $this->info("Menjalankan migration ke {$tenants->count()} tenant...");
        $this->newLine();

        $success = 0;
        $failed  = 0;

        foreach ($tenants as $tenant) {
            $this->line("  <fg=cyan>→</> {$tenant->subdomain} ({$tenant->db_name})");

            $result = $fresh
                ? $this->migrateFresh($tenant, $manager)
                : $manager->migrate($tenant);

            if ($result['success']) {
                $this->line("    <fg=green>✓ Berhasil</>");
                $success++;
            } else {
                $this->line("    <fg=red>✗ Gagal: {$result['output']}</>");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Selesai. Berhasil: {$success} | Gagal: {$failed}");

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    private function migrateFresh(Tenant $tenant, TenantManager $manager): array
    {
        if (!$this->confirm("Fresh migrate {$tenant->subdomain}? Semua data akan dihapus!", false)) {
            return ['success' => false, 'output' => 'Dibatalkan'];
        }
        // Panggil migrate biasa — fresh migrate bisa diimplementasi di TenantManager jika perlu
        return $manager->migrate($tenant);
    }
}
