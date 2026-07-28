<?php

namespace App\Console\Commands;

use App\Support\RankingCache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoSyncPicMarketingPoints extends Command
{
    /**
     * @var string
     */
    protected $signature = 'points:auto-sync';

    /**
     * @var string
     */
    protected $description = 'Sinkronkan otomatis total_points PIC & Marketing HANYA dari riwayat poin yang SUDAH ADA (tidak membuat riwayat baru) — jaring pengaman berkala untuk kasus seperti riwayat tersimpan benar tapi total_points tidak ikut ter-update. TIDAK melakukan backfill data lama — itu tugas tombol manual "Sinkronisasi Point" di /admin/sync.';

    public function handle(): int
    {
        // HANYA recompute total_points dari SUM riwayat yang SUDAH ADA di
        // pic_point_histories/marketing_point_histories — TIDAK PERNAH membuat baris
        // riwayat baru dari submission lama. Ini penting: kalau admin sengaja mereset
        // semua poin ke 0 (hapus semua riwayat), auto-sync ini TIDAK BOLEH menghidupkan
        // kembali data lama tersebut — cukup total_points tetap 0 karena memang tidak
        // ada riwayat sama sekali. Backfill data lama dari submission adalah tindakan
        // SENGAJA yang cuma boleh dipicu manual lewat tombol "Sinkronisasi Point" di
        // /admin/sync (PicPointReportController::runFullSync() / runBulkSync()).
        $picSynced = DB::affectingStatement('
            UPDATE pics p
            LEFT JOIN (
                SELECT pic_id, COALESCE(SUM(points_earned), 0) AS actual
                FROM pic_point_histories
                GROUP BY pic_id
            ) h ON h.pic_id = p.id
            SET p.total_points = COALESCE(h.actual, 0)
            WHERE p.total_points != COALESCE(h.actual, 0)
        ');

        $mktSynced = DB::affectingStatement('
            UPDATE marketings m
            LEFT JOIN (
                SELECT marketing_id, COALESCE(SUM(points_earned), 0) AS actual
                FROM marketing_point_histories
                GROUP BY marketing_id
            ) h ON h.marketing_id = m.id
            SET m.total_points = COALESCE(h.actual, 0)
            WHERE m.total_points != COALESCE(h.actual, 0)
        ');

        if ($picSynced > 0 || $mktSynced > 0) {
            RankingCache::forgetPics();
            RankingCache::forgetMarketings();
            Cache::forget('sync.out_of_sync_count');

            $pesan = "Auto-sync poin: {$picSynced} PIC dan {$mktSynced} marketing di-sinkronkan ulang "
                . "total_points-nya dari riwayat yang sudah ada (tidak ada riwayat baru dibuat).";

            Log::info($pesan);
            $this->info($pesan);
        } else {
            $this->info('Auto-sync poin: semua PIC & Marketing sudah sinkron, tidak ada yang perlu dikoreksi.');
        }

        // Catat kapan & apa hasil auto-sync TERAKHIR berjalan — dipakai halaman
        // /admin/sync untuk menunjukkan indikator "terakhir berjalan kapan", supaya
        // admin bisa memastikan cron scheduler benar-benar aktif tanpa perlu buka
        // storage/logs/laravel.log atau SSH ke server. TTL panjang (1 hari) supaya
        // tetap terbaca walau auto-sync sempat berhenti jalan.
        Cache::put('points.auto_sync.last_run_at', now(), now()->addDay());
        Cache::put('points.auto_sync.last_result', [
            'pic_synced' => $picSynced,
            'mkt_synced' => $mktSynced,
        ], now()->addDay());

        return self::SUCCESS;
    }
}
