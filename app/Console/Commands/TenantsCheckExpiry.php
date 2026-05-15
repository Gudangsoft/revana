<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\Tenant;
use App\Services\FonnteService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TenantsCheckExpiry extends Command
{
    protected $signature   = 'tenants:check-expiry';
    protected $description = 'Cek masa aktif tenant: suspend yang expired, kirim notif WA yang akan habis';

    public function handle(): int
    {
        $suspended   = 0;
        $notified    = 0;
        $superPhone  = Setting::get('super_admin_phone', '');

        foreach (Tenant::whereNotIn('status', ['suspended'])->get() as $tenant) {

            // Lifetime plan → tidak perlu cek expired
            if ($tenant->plan === 'lifetime') continue;

            // ── Tentukan tanggal referensi berdasarkan status ──────────────
            $expDate = $tenant->status === 'trial'
                ? $tenant->trial_ends_at
                : $tenant->expires_at;

            if (!$expDate) continue;

            $daysLeft = (int) now()->diffInDays($expDate, false); // negatif jika sudah lewat

            // ── Sudah expired → suspend otomatis ──────────────────────────
            if ($daysLeft < 0) {
                $tenant->update(['status' => 'expired']);
                $suspended++;
                $this->line("  <fg=red>✗ Expired:</> {$tenant->subdomain} (was {$tenant->status})");

                if ($superPhone) {
                    $this->notifySuperAdmin($superPhone, $tenant, 'expired');
                }
                continue;
            }

            // ── Akan expired dalam 1, 3, atau 7 hari → notif super admin ──
            if (in_array($daysLeft, [7, 3, 1]) && $superPhone) {
                $this->notifySuperAdmin($superPhone, $tenant, 'warning', $daysLeft);
                $notified++;
                $this->line("  <fg=yellow>⚠ Notified:</> {$tenant->subdomain} ({$daysLeft} hari lagi)");
            }
        }

        $this->info("Selesai. Suspended: {$suspended} | Notifikasi terkirim: {$notified}");
        return Command::SUCCESS;
    }

    private function notifySuperAdmin(string $phone, Tenant $tenant, string $type, int $days = 0): void
    {
        try {
            $fonnte = new FonnteService();
            if (!$fonnte->isConfigured()) return;

            if ($type === 'expired') {
                $msg = "⚠️ *SIPERA Tenant Alert*\n\n"
                     . "Tenant *{$tenant->name}* ({$tenant->subdomain}.apji.org) "
                     . "telah *kedaluwarsa* dan otomatis di-suspend.\n\n"
                     . "Paket: " . ucfirst($tenant->plan) . "\n"
                     . "Email: {$tenant->email}\n\n"
                     . "Silakan perbarui paket jika klien ingin melanjutkan layanan.";
            } else {
                $msg = "🔔 *SIPERA Tenant Reminder*\n\n"
                     . "Masa aktif tenant *{$tenant->name}* ({$tenant->subdomain}.apji.org) "
                     . "akan berakhir dalam *{$days} hari*.\n\n"
                     . "Paket: " . ucfirst($tenant->plan) . "\n"
                     . "Email: {$tenant->email}\n\n"
                     . "Segera perpanjang agar klien tidak kehilangan akses.";
            }

            $fonnte->send($phone, $msg);
        } catch (\Exception $e) {
            Log::warning("TenantsCheckExpiry: gagal kirim WA ke {$phone}: {$e->getMessage()}");
        }
    }
}
