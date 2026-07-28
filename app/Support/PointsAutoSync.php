<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Logika inti auto-sync poin PIC & Marketing — dipakai BERSAMA oleh command
 * terjadwal (points:auto-sync, kalau cron server aktif) dan pemicu tanpa-cron di
 * AdminMiddleware (dibatasi 1x/15 menit, jalan begitu ada admin membuka halaman
 * admin apa pun). Dua jalur pemicu, satu logika — supaya perilakunya selalu sama
 * persis di mana pun dipanggil.
 *
 * PENTING: HANYA recompute total_points dari SUM riwayat yang SUDAH ADA — TIDAK
 * PERNAH membuat baris riwayat baru dari submission lama (lihat insiden 28 Juli
 * 2026: versi awal auto-sync sempat backfill data lama, menghidupkan kembali poin
 * yang sengaja direset admin). Backfill data lama sekarang murni tindakan manual
 * lewat TaskPointSettingController (dipicu saat admin mengubah rate poin) — bukan
 * lagi lewat mekanisme otomatis apa pun.
 */
class PointsAutoSync
{
    private const THROTTLE_MINUTES = 15;

    /**
     * Jalankan sinkronisasi sekarang, tanpa peduli kapan terakhir jalan.
     * Returns [$picSynced, $mktSynced].
     */
    public static function run(): array
    {
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

            Log::info("Auto-sync poin: {$picSynced} PIC dan {$mktSynced} marketing di-sinkronkan ulang "
                . "total_points-nya dari riwayat yang sudah ada (tidak ada riwayat baru dibuat).");
        }

        Cache::put('points.auto_sync.last_run_at', now(), now()->addDay());
        Cache::put('points.auto_sync.last_result', [
            'pic_synced' => $picSynced,
            'mkt_synced' => $mktSynced,
        ], now()->addDay());

        return [$picSynced, $mktSynced];
    }

    /**
     * Jalankan HANYA kalau sudah lebih dari THROTTLE_MINUTES sejak terakhir jalan —
     * dipakai pemicu tanpa-cron (AdminMiddleware) supaya tidak jalan di SETIAP
     * request admin, cukup sesekali. Pakai Cache::add() (atomik) sebagai kunci
     * throttle terpisah dari last_run_at, supaya request yang datang BERSAMAAN tidak
     * ikut lolos throttle sebelum run() sempat menulis last_run_at yang baru.
     */
    public static function runIfDue(): void
    {
        if (!Cache::add('points.auto_sync.throttle_lock', true, now()->addMinutes(self::THROTTLE_MINUTES))) {
            return;
        }

        self::run();
    }
}
