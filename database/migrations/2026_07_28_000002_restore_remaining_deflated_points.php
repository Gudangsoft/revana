<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Lanjutan dari 2026_07_28_000001_restore_points_corrupted_by_rewrite_bug.php.
     *
     * Migration sebelumnya cuma memulihkan baris yang TERBUKTI ditimpa ulang setelah
     * dibuat (updated_at > created_at). Ternyata ada jalur lain yang tidak tertangkap
     * sinyal itu: baris yang di-BACKFILL (diisi otomatis lewat runBulkSync() untuk
     * submission lama yang belum punya riwayat) PADA SAAT rate poin sudah rusak/rendah
     * — baris ini baru dibuat dengan nilai yang sudah salah sejak awal, jadi
     * created_at == updated_at (tidak pernah "ditimpa", karena memang salah sejak
     * lahir), lolos dari filter migration sebelumnya. Contoh nyata: marketing "Risqi"
     * (id=10) masih punya 930 dari 2.245 baris riwayat bernilai 0,25 setelah migration
     * pertama, seharusnya 1.
     *
     * Migration ini memperbaiki SEMUA baris yang terhubung ke submission asli
     * (submission_id terisi) yang nilainya BUKAN nilai standar — tanpa syarat updated_at
     * lagi. Penyesuaian manual admin (submission_id NULL) tetap TIDAK PERNAH disentuh.
     *
     * Nilai standar Marketing SENGAJA diubah user jadi 0,5 poin/submission (kebijakan
     * baru, bukan 1) — TaskPointSetting untuk marketing 'submit' juga disamakan ke 0,5
     * di sini supaya penghargaan poin ke depan konsisten dengan riwayat yang dipulihkan.
     * PIC TIDAK berubah (tetap 1 poin/tugas, 0 untuk step 'validator').
     */
    public function up(): void
    {
        DB::transaction(function () {
            // Samakan rate marketing submit ke 0,5 supaya penghargaan poin BARU juga
            // konsisten dengan riwayat yang dipulihkan (bukan cuma perbaikan riwayat lama).
            \App\Models\TaskPointSetting::updateOrCreate(
                ['user_type' => 'marketing', 'task_key' => 'submit'],
                ['task_label' => 'Submit Artikel', 'points' => 0.5, 'is_active' => true]
            );

            $marketingRowsFixed = DB::affectingStatement("
                UPDATE marketing_point_histories
                SET points_earned = 0.5
                WHERE submission_id IS NOT NULL AND ABS(points_earned - 0.5) > 0.0001
            ");

            $marketingTotalsFixed = DB::affectingStatement('
                UPDATE marketings m
                SET m.total_points = (
                    SELECT COALESCE(SUM(mph.points_earned), 0)
                    FROM marketing_point_histories mph
                    WHERE mph.marketing_id = m.id
                )
            ');

            $picRowsFixed = DB::affectingStatement("
                UPDATE pic_point_histories
                SET points_earned = CASE WHEN step = 'validator' THEN 0 ELSE 1 END
                WHERE submission_id IS NOT NULL
                  AND ABS(points_earned - (CASE WHEN step = 'validator' THEN 0 ELSE 1 END)) > 0.0001
            ");

            $picTotalsFixed = DB::affectingStatement('
                UPDATE pics p
                SET p.total_points = (
                    SELECT COALESCE(SUM(h.points_earned), 0)
                    FROM pic_point_histories h
                    WHERE h.pic_id = p.id
                )
            ');

            Log::info('Restore lanjutan: baris poin yang salah sejak backfill (bukan cuma yang ditimpa ulang)', [
                'marketing_rows_restored' => $marketingRowsFixed,
                'marketing_totals_recalculated' => $marketingTotalsFixed,
                'pic_rows_restored' => $picRowsFixed,
                'pic_totals_recalculated' => $picTotalsFixed,
            ]);
        });
    }

    public function down(): void
    {
        // Sengaja kosong — nilai sebelum migration ini sudah salah, tidak ada gunanya dikembalikan.
    }
};
