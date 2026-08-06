<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Permintaan eksplisit user 31 Juli 2026 (log-update-2026-07-31.md #4): kembalikan
     * riwayat poin PIC/Marketing dari SEBELUM reset 28 Juli 2026 supaya laporan (mis.
     * Laporan Kinerja bulan Februari 2026) tidak lagi menampilkan Total Poin 0. Ini
     * SENGAJA membatalkan efek "Reset Semua Point" (dan migration
     * 2026_07_29_000001_remove_points_resurrected_after_intentional_reset) — user sudah
     * dikonfirmasi dan memahami konsekuensinya: Total Points PIC/Marketing yang aktif
     * akan naik kembali ke angka sebelum reset.
     *
     * Sumber data: tabel backup `pic_point_histories_backup_20260729` /
     * `marketing_point_histories_backup_20260729` yang dibuat migration 29 Juli di atas
     * sebelum menghapus baris-baris itu. Kolom `id` SENGAJA tidak ikut disalin (INSERT
     * pakai daftar kolom eksplisit) supaya auto-increment memberi id baru — id lama di
     * backup tumpang tindih dengan id yang sudah dipakai baris live pasca-reset
     * (live: 335151-468334, backup: 335156-466137), jadi menyalin id apa adanya akan
     * gagal / diam-diam melewati baris yang seharusnya masuk kalau pakai INSERT IGNORE.
     *
     * `points_reset_at` dikosongkan (NULL) untuk semua PIC/Marketing setelah restore —
     * boundary itu hanya berguna untuk menahan backfill agar tidak membangun ulang
     * riwayat yang SENGAJA dihapus; setelah riwayat itu sendiri dikembalikan secara utuh,
     * boundary-nya tidak relevan lagi.
     */
    private const RESET_AT = '2026-07-28 21:59:35';

    public function up(): void
    {
        DB::transaction(function () {
            $picInserted = DB::affectingStatement('
                INSERT IGNORE INTO pic_point_histories
                    (pic_id, submission_id, step, points_earned, description, created_at, updated_at)
                SELECT pic_id, submission_id, step, points_earned, description, created_at, updated_at
                FROM pic_point_histories_backup_20260729
            ');

            $mktInserted = DB::affectingStatement('
                INSERT IGNORE INTO marketing_point_histories
                    (marketing_id, submission_id, points_earned, description, created_at, updated_at)
                SELECT marketing_id, submission_id, points_earned, description, created_at, updated_at
                FROM marketing_point_histories_backup_20260729
            ');

            DB::statement('
                UPDATE pics p
                SET p.total_points = (
                    SELECT COALESCE(SUM(h.points_earned), 0)
                    FROM pic_point_histories h
                    WHERE h.pic_id = p.id
                ),
                p.points_reset_at = NULL
            ');

            DB::statement('
                UPDATE marketings m
                SET m.total_points = (
                    SELECT COALESCE(SUM(mph.points_earned), 0)
                    FROM marketing_point_histories mph
                    WHERE mph.marketing_id = m.id
                ),
                m.points_reset_at = NULL
            ');

            Log::info('Restore riwayat poin pra-reset atas permintaan eksplisit user (31 Juli 2026)', [
                'pic_rows_inserted' => $picInserted,
                'marketing_rows_inserted' => $mktInserted,
                'source_backup_tables' => ['pic_point_histories_backup_20260729', 'marketing_point_histories_backup_20260729'],
            ]);
        });
    }

    /**
     * Batalkan restore: hapus lagi baris pra-reset, kembalikan points_reset_at ke
     * boundary semula, hitung ulang total_points. Menyamai kondisi tepat sebelum
     * migration ini dijalankan.
     */
    public function down(): void
    {
        DB::transaction(function () {
            $picDeleted = DB::table('pic_point_histories')
                ->where('created_at', '<', self::RESET_AT)
                ->delete();

            $mktDeleted = DB::table('marketing_point_histories')
                ->where('created_at', '<', self::RESET_AT)
                ->delete();

            DB::statement('
                UPDATE pics p
                SET p.total_points = (
                    SELECT COALESCE(SUM(h.points_earned), 0)
                    FROM pic_point_histories h
                    WHERE h.pic_id = p.id
                ),
                p.points_reset_at = \'' . self::RESET_AT . '\'
            ');

            DB::statement('
                UPDATE marketings m
                SET m.total_points = (
                    SELECT COALESCE(SUM(mph.points_earned), 0)
                    FROM marketing_point_histories mph
                    WHERE mph.marketing_id = m.id
                ),
                m.points_reset_at = \'' . self::RESET_AT . '\'
            ');

            Log::info('Rollback: pembatalan restore riwayat poin pra-reset (31 Juli 2026)', [
                'pic_rows_deleted' => $picDeleted,
                'marketing_rows_deleted' => $mktDeleted,
            ]);
        });
    }
};
