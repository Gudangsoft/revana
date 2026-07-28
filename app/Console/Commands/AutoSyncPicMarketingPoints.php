<?php

namespace App\Console\Commands;

use App\Support\PointsAutoSync;
use Illuminate\Console\Command;

class AutoSyncPicMarketingPoints extends Command
{
    /**
     * @var string
     */
    protected $signature = 'points:auto-sync';

    /**
     * @var string
     */
    protected $description = 'Sinkronkan otomatis total_points PIC & Marketing HANYA dari riwayat poin yang SUDAH ADA (tidak membuat riwayat baru) — jaring pengaman berkala untuk kasus seperti riwayat tersimpan benar tapi total_points tidak ikut ter-update. Dipakai scheduler kalau cron server aktif; kalau tidak, AdminMiddleware jadi jaring pengaman lewat PointsAutoSync::runIfDue().';

    public function handle(): int
    {
        [$picSynced, $mktSynced] = PointsAutoSync::run();

        if ($picSynced > 0 || $mktSynced > 0) {
            $this->info("Auto-sync poin: {$picSynced} PIC dan {$mktSynced} marketing di-sinkronkan ulang "
                . "total_points-nya dari riwayat yang sudah ada (tidak ada riwayat baru dibuat).");
        } else {
            $this->info('Auto-sync poin: semua PIC & Marketing sudah sinkron, tidak ada yang perlu dikoreksi.');
        }

        return self::SUCCESS;
    }
}
