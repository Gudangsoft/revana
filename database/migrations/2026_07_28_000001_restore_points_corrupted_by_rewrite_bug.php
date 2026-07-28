<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Perbaikan darurat untuk insiden poin PIC/Marketing anjlok (28 Juli 2026).
     *
     * Root cause: PicPointReportController::runBulkSync() dan
     * MarketingPointReportController::runBulkSync() (dipanggil OTOMATIS setiap admin
     * menyimpan setting poin tugas APAPUN di /admin/task-point-settings, tidak cuma
     * setting yang relevan) dulu punya query yang MENIMPA ULANG `points_earned` pada
     * SEMUA baris riwayat yang SUDAH ADA supaya sama dengan rate yang berlaku SAAT ITU
     * — menghancurkan nilai historis asli. Ini sudah diperbaiki di kode (lihat commit
     * hari ini) supaya HANYA mengisi baris yang belum ada, tidak pernah lagi menimpa
     * baris yang sudah ada.
     *
     * Migration ini memulihkan data yang SUDAH TERLANJUR rusak akibat bug lama itu.
     * Baris yang terbukti pernah ditimpa (updated_at > created_at, dan submission_id
     * terisi — bukan penyesuaian manual admin yang submission_id-nya NULL) dikembalikan
     * ke nilai poin standar sesuai desain awal sistem ini ("1 submission/tugas = 1
     * poin", validator = 0 poin). Baris yang TIDAK PERNAH ditimpa (updated_at ==
     * created_at) TIDAK disentuh sama sekali — nilai aslinya (apa pun itu) dibiarkan
     * apa adanya.
     */
    public function up(): void
    {
        DB::transaction(function () {
            // --- Marketing: pulihkan baris yang terbukti pernah ditimpa ---
            $marketingAffectedBefore = DB::select("
                SELECT marketing_id, id, points_earned
                FROM marketing_point_histories
                WHERE submission_id IS NOT NULL AND updated_at > created_at
            ");

            $marketingRowsFixed = DB::affectingStatement("
                UPDATE marketing_point_histories
                SET points_earned = 1, updated_at = updated_at
                WHERE submission_id IS NOT NULL AND updated_at > created_at
            ");

            $marketingTotalsFixed = DB::affectingStatement('
                UPDATE marketings m
                SET m.total_points = (
                    SELECT COALESCE(SUM(mph.points_earned), 0)
                    FROM marketing_point_histories mph
                    WHERE mph.marketing_id = m.id
                )
            ');

            // --- PIC: pulihkan baris yang terbukti pernah ditimpa ---
            $picAffectedBefore = DB::select("
                SELECT pic_id, id, step, points_earned
                FROM pic_point_histories
                WHERE submission_id IS NOT NULL AND updated_at > created_at
            ");

            $picRowsFixed = DB::affectingStatement("
                UPDATE pic_point_histories
                SET points_earned = CASE WHEN step = 'validator' THEN 0 ELSE 1 END,
                    updated_at = updated_at
                WHERE submission_id IS NOT NULL AND updated_at > created_at
            ");

            $picTotalsFixed = DB::affectingStatement('
                UPDATE pics p
                SET p.total_points = (
                    SELECT COALESCE(SUM(h.points_earned), 0)
                    FROM pic_point_histories h
                    WHERE h.pic_id = p.id
                )
            ');

            Log::info('Restore poin PIC/Marketing yang rusak akibat bug penimpaan ulang riwayat', [
                'marketing_rows_restored' => $marketingRowsFixed,
                'marketing_totals_recalculated' => $marketingTotalsFixed,
                'marketing_affected_marketing_ids' => collect($marketingAffectedBefore)->pluck('marketing_id')->unique()->values(),
                'pic_rows_restored' => $picRowsFixed,
                'pic_totals_recalculated' => $picTotalsFixed,
                'pic_affected_pic_ids' => collect($picAffectedBefore)->pluck('pic_id')->unique()->values(),
            ]);
        });
    }

    /**
     * Tidak reversible dengan tepat — nilai sebelum migration ini sudah rusak (hasil
     * bug), tidak ada gunanya dikembalikan ke sana. Lihat catatan di up().
     */
    public function down(): void
    {
        // Sengaja kosong — lihat catatan di atas.
    }
};
