<?php

namespace App\Services;

use App\Models\Marketing;
use App\Models\MarketingPointHistory;
use App\Models\Pic;
use App\Models\PicPointHistory;
use App\Support\RankingCache;
use Illuminate\Support\Facades\DB;

/**
 * Satu-satunya jalur resmi untuk memberi & mencabut poin PIC/Marketing.
 *
 * Fase 1 dari konsolidasi 29 Juli 2026 (lihat docs/tests/log-update-2026-07-29.md):
 * sebelum ini, logika award/revoke ditulis ulang di 23+ titik panggilan tersebar di
 * 4 controller berbeda — setiap kali satu titik diperbaiki, titik lain yang mirip
 * tapi tidak ikut disentuh tetap membawa bug yang sama (kasus Aji, BKD, fasttrack
 * admin, dst, semua hari ini). `PicPointHistory::awardPoints()`/`revokePoints()` dan
 * `MarketingPointHistory::awardPoints()` sekarang jadi delegate tipis ke sini —
 * perbaikan logika ke depan cukup di file ini.
 *
 * `revokeFromMarketing()` BARU dibuat di sini — sebelumnya Marketing tidak punya
 * kemampuan cabut poin sama sekali (asimetri dengan PIC yang sudah lama punya
 * `revokePoints()`), salah satu penyebab `SubmissionController::destroy()` cuma
 * mencabut poin Marketing dan diam-diam membiarkan poin PIC "bocor" (tetap ada
 * walau submission-nya sudah dihapus).
 *
 * Method bulk/backfill (`runBulkSync()` di PicPointReportController/
 * MarketingPointReportController, `PointsAutoSync::run()`) BELUM dipindah ke sini —
 * itu Fase 4 yang sengaja ditunda terpisah karena risikonya lebih tinggi (kode
 * itu persis yang menyebabkan insiden resurrection 29 Juli).
 */
class PointsService
{
    /**
     * Beri poin ke PIC untuk menyelesaikan satu tahap.
     *
     * $occurredAt: tanggal SEBENARNYA tugas ini selesai (mis. submission->created_at
     * untuk step 'submit', atau kolom *_validated_at untuk step lain). Wajib diisi oleh
     * pemanggil yang bersifat backfill/sync (bukan event langsung saat itu juga) — kalau
     * dibiarkan null, timestamp riwayat akan memakai waktu SEKARANG (saat fungsi ini
     * dipanggil), yang salah untuk sync karena tanggal penyelesaian tugas jadi ikut
     * berubah ke tanggal sync, bukan tanggal tugas benar-benar selesai.
     */
    public static function awardToPic(int $picId, int $submissionId, string $step, ?string $description = null, $occurredAt = null): ?PicPointHistory
    {
        $points = PicPointHistory::getPointsForStep($step);

        if ($points <= 0) {
            return null;
        }

        // Check if points already awarded for this submission + step + pic
        $existing = PicPointHistory::where('pic_id', $picId)
            ->where('submission_id', $submissionId)
            ->where('step', $step)
            ->first();

        if ($existing) {
            return null; // Already awarded
        }

        // Create point history. Pengecekan di atas TIDAK atomik — kalau 2 permintaan
        // datang hampir bersamaan (klik ganda, retry jaringan), keduanya bisa lolos
        // pengecekan sebelum salah satunya sempat tersimpan. UNIQUE index
        // (pic_id, submission_id, step) di database jadi penjaga terakhir; kalau
        // race itu terjadi, INSERT kedua akan gagal dengan duplicate-key — tangkap
        // di sini dan perlakukan sama seperti "sudah pernah diberi" (return null),
        // bukan crash 500.
        //
        // create() + backdate created_at + increment total_points DIBUNGKUS 1
        // TRANSAKSI supaya atomik — insiden nyata: riwayat berhasil tersimpan
        // (points_earned benar) tapi total_points PIC tidak ikut bertambah, karena
        // sebelumnya increment() ada di baris TERPISAH setelah forceFill()->save();
        // kalau ada apa pun yang gagal di antara create() dan increment(), riwayat
        // sudah kadung tersimpan tapi total_points tidak ikut ter-update — state
        // tidak konsisten yang butuh sinkronisasi manual untuk diperbaiki. Dengan
        // transaksi, create()+backdate+increment sekarang selalu semua berhasil
        // atau semua batal bersama.
        try {
            $history = DB::transaction(function () use ($picId, $submissionId, $step, $description, $points, $occurredAt) {
                $history = PicPointHistory::create([
                    'pic_id' => $picId,
                    'submission_id' => $submissionId,
                    'step' => $step,
                    'points_earned' => $points,
                    'description' => $description ?? "Menyelesaikan tugas " . PicPointHistory::getLabelForStep($step),
                ]);

                if ($occurredAt) {
                    $history->forceFill([
                        'created_at' => $occurredAt,
                        'updated_at' => $occurredAt,
                    ])->save();
                }

                // Update total points on PIC
                Pic::where('id', $picId)->increment('total_points', $points);

                return $history;
            });
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'pic_point_histories_unique_award')) {
                return null;
            }
            throw $e;
        }

        RankingCache::forgetPics();

        return $history;
    }

    /**
     * Beri poin ke Marketing untuk submission yang berhasil.
     *
     * $occurredAt: sama seperti awardToPic() — tanggal ASLI submission, bukan waktu sync.
     */
    public static function awardToMarketing(int $marketingId, int $submissionId, ?string $description = null, $occurredAt = null): ?MarketingPointHistory
    {
        // Check if points already awarded for this submission
        $existing = MarketingPointHistory::where('marketing_id', $marketingId)
            ->where('submission_id', $submissionId)
            ->first();

        if ($existing) {
            return null; // Already awarded
        }

        $points = MarketingPointHistory::getPointsForSubmission();

        // Create point history. Kalau 2 request datang hampir bersamaan (race condition:
        // klik ganda, retry jaringan), keduanya bisa lolos pengecekan "sudah ada atau
        // belum" di atas sebelum salah satu sempat tersimpan — constraint unique
        // (marketing_id, submission_id) di database akan menolak yang kedua. Tangkap di
        // sini supaya user dapat respons mulus (null, "sudah pernah diberi"), bukan
        // crash 500 — sama seperti penanganan di awardToPic().
        //
        // create() + backdate created_at + recompute total_points DIBUNGKUS 1 TRANSAKSI
        // supaya atomik — kalau ada apa pun yang gagal di antara create() dan recompute
        // (mis. forceFill()->save() untuk backdate), riwayat sudah kadung tersimpan tapi
        // total_points tidak ikut ter-update, persis insiden nyata yang pernah ditemukan
        // di sisi PIC (riwayat benar, total_points PIC tidak ikut bertambah).
        try {
            $history = DB::transaction(function () use ($marketingId, $submissionId, $description, $points, $occurredAt) {
                $history = MarketingPointHistory::create([
                    'marketing_id' => $marketingId,
                    'submission_id' => $submissionId,
                    'points_earned' => $points,
                    'description' => $description ?? "Submit artikel berhasil",
                ]);

                if ($occurredAt) {
                    $history->forceFill([
                        'created_at' => $occurredAt,
                        'updated_at' => $occurredAt,
                    ])->save();
                }

                // Sync total_points dari SUM riwayat poin — BUKAN COUNT submission, karena
                // rate poin per submission bisa berubah dari waktu ke waktu (lihat
                // TaskPointSetting), jadi COUNT tidak akan cocok dengan total yang
                // sebenarnya pernah diberikan.
                $actualPoints = MarketingPointHistory::where('marketing_id', $marketingId)->sum('points_earned');
                Marketing::where('id', $marketingId)->update(['total_points' => $actualPoints]);

                return $history;
            });
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'marketing_point_histories_marketing_id_submission_id_unique')) {
                return null;
            }
            throw $e;
        }

        RankingCache::forgetMarketings();

        return $history;
    }

    /**
     * Cabut poin PIC untuk satu tahap (mis. validasi dibatalkan/di-uncheck).
     */
    public static function revokeFromPic(int $picId, int $submissionId, string $step): bool
    {
        $history = PicPointHistory::where('pic_id', $picId)
            ->where('submission_id', $submissionId)
            ->where('step', $step)
            ->first();

        if (!$history) {
            return false;
        }

        $points = $history->points_earned;
        $history->delete();

        // Decrement but never go below 0
        Pic::where('id', $picId)
            ->where('total_points', '>=', $points)
            ->decrement('total_points', $points);

        // Safety: recalculate from history sum to avoid drift
        $actual = PicPointHistory::where('pic_id', $picId)->sum('points_earned');
        Pic::where('id', $picId)->update(['total_points' => max(0, $actual)]);

        RankingCache::forgetPics();

        return true;
    }

    /**
     * Cabut poin Marketing untuk satu submission.
     *
     * BARU di Fase 1 konsolidasi ini — sebelumnya Marketing tidak punya method setara
     * `PicPointHistory::revokePoints()` sama sekali, jadi tidak ada satu pun titik kode
     * yang bisa mencabut poin Marketing untuk 1 submission tertentu (bandingkan dengan
     * `SubmissionController::destroy()` yang MENGHAPUS baris MarketingPointHistory
     * secara manual — bukan lewat method bersama seperti ini).
     */
    public static function revokeFromMarketing(int $marketingId, int $submissionId): bool
    {
        $history = MarketingPointHistory::where('marketing_id', $marketingId)
            ->where('submission_id', $submissionId)
            ->first();

        if (!$history) {
            return false;
        }

        $points = $history->points_earned;
        $history->delete();

        // Decrement but never go below 0
        Marketing::where('id', $marketingId)
            ->where('total_points', '>=', $points)
            ->decrement('total_points', $points);

        // Safety: recalculate from history sum to avoid drift
        $actual = MarketingPointHistory::where('marketing_id', $marketingId)->sum('points_earned');
        Marketing::where('id', $marketingId)->update(['total_points' => max(0, $actual)]);

        RankingCache::forgetMarketings();

        return true;
    }

    /**
     * Cabut SEMUA poin (PIC + Marketing) yang terkait 1 submission — dipanggil SEBELUM
     * submission dihapus.
     *
     * Fase 3 konsolidasi 29 Juli 2026: menutup celah nyata di
     * `SubmissionController::destroy()`/`fasttrackDestroy()` — sebelumnya `destroy()`
     * cuma mencabut poin Marketing (manual, hapus baris `MarketingPointHistory` langsung
     * di controller, bukan lewat method bersama), dan sama sekali TIDAK menyentuh poin
     * PIC — jadi poin PIC "bocor" (tetap ada) walau submission-nya sudah dihapus.
     * `fasttrackDestroy()` malah tidak mencabut poin sama sekali (PIC maupun Marketing).
     *
     * WAJIB dipanggil SEBELUM `$submission->delete()` — FK `pic_point_histories.
     * submission_id` adalah `onDelete('set null')`, jadi setelah submission dihapus,
     * baris riwayat PIC-nya tidak bisa lagi ditemukan lewat `submission_id` (jadi baris
     * yatim yang tidak pernah tercabut).
     *
     * Bisa ada LEBIH DARI SATU baris PicPointHistory untuk 1 submission (beda PIC/step
     * di tiap tahap workflow) — semuanya dicabut, bukan cuma satu. Marketing hanya
     * pernah ada maksimal 1 baris per submission (unique constraint
     * marketing_id+submission_id), jadi cukup 1 pencarian.
     *
     * Return ['pic' => n, 'marketing' => n] — jumlah baris yang benar-benar tercabut,
     * untuk logging/pesan flash. Aman dipanggil untuk submission yang tidak pernah
     * dapat poin sama sekali (return 0/0, tidak error) atau dipanggil dua kali untuk
     * submission yang sama (panggilan kedua tidak menemukan apa-apa lagi, return 0/0).
     */
    public static function revokeAllForSubmission(int $submissionId): array
    {
        $revoked = ['pic' => 0, 'marketing' => 0];

        DB::transaction(function () use ($submissionId, &$revoked) {
            $picRows = PicPointHistory::where('submission_id', $submissionId)->get(['pic_id', 'step']);
            foreach ($picRows as $row) {
                if (self::revokeFromPic($row->pic_id, $submissionId, $row->step)) {
                    $revoked['pic']++;
                }
            }

            $marketingRow = MarketingPointHistory::where('submission_id', $submissionId)->first(['marketing_id']);
            if ($marketingRow && self::revokeFromMarketing($marketingRow->marketing_id, $submissionId)) {
                $revoked['marketing']++;
            }
        });

        return $revoked;
    }
}
