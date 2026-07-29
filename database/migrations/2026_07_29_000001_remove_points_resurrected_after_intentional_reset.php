<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Perbaikan darurat insiden 29 Juli 2026: admin sengaja menjalankan "Reset Semua
     * Point" untuk PIC & Marketing (~28 Juli 2026 21:59:35, dikonfirmasi dari klaster
     * `updated_at` identik pada 38 baris `pics`) supaya poin mulai dihitung ulang dari
     * 0 sejak tanggal itu. Riwayat sengaja dikosongkan (`->delete()`), tapi kolom
     * `submissions.marketing_id`/`petugas_*_id` (sumber data yang dipakai backfill)
     * tidak ikut terhapus.
     *
     * Belakangan, admin diarahkan (rekomendasi keliru dari asisten) untuk menyimpan
     * ulang pengaturan di /admin/task-point-settings guna mem-backfill poin yang
     * hilang akibat bug lain (lihat log-update-2026-07-29.md #1-#2). Ini TANPA SENGAJA
     * memicu `PicPointReportController::runBulkSync()` / `MarketingPointReportController::
     * runBulkSync()`, yang membangun ulang SEMUA baris riwayat lama (pra-reset) karena
     * query backfill-nya generic (submission yang belum punya baris riwayat = dianggap
     * "belum tercatat", tanpa tahu itu sebelumnya SENGAJA dihapus lewat reset).
     *
     * Root cause SAMA seperti insiden 28 Juli yang sudah didokumentasikan di
     * `App\Support\PointsAutoSync` (constructor docblock) — makanya jalur otomatis
     * (auto-sync tanpa cron) sudah sengaja dibuat TIDAK PERNAH backfill. Sayangnya jalur
     * `TaskPointSettingController::syncTotals()` (dipicu simpan setting rate) masih
     * memanggil `runBulkSync()` yang backfill tanpa penjagaan serupa.
     *
     * Migration ini menghapus HANYA baris riwayat yang `created_at`-nya (tanggal ASLI
     * tugas/submission, bukan tanggal baris riwayat dibuat) SEBELUM momen reset —
     * baris itu pasti hasil "hidup lagi", karena kalau reset benar-benar mengosongkan
     * semua riwayat, tidak mungkin ada riwayat bertanggal SEBELUM reset yang masih sah
     * ada di tabel setelah reset kecuali dibuat ulang oleh backfill. Baris yang
     * `created_at`-nya SETELAH momen reset (aktivitas asli pasca-reset, termasuk yang
     * baru sempat tercatat lewat backfill karena bug lain yang legitimate) TIDAK
     * disentuh.
     *
     * Skala dikonfirmasi via query manual sebelum migration ini ditulis:
     * - Marketing: 14.156 baris / 7.078,00 poin / 14 marketing terdampak.
     * - PIC: 97.832 baris / 28.263,03 poin / 54 PIC terdampak.
     */
    private const RESET_AT = '2026-07-28 21:59:35';

    public function up(): void
    {
        // Backup dulu (DDL — SENGAJA di luar transaksi, lihat pelajaran dari migration
        // 2026_07_28_000005 soal implicit commit kalau DDL dicampur transaksi manual).
        DB::statement('DROP TABLE IF EXISTS pic_point_histories_backup_20260729');
        DB::statement('DROP TABLE IF EXISTS marketing_point_histories_backup_20260729');
        DB::statement("
            CREATE TABLE pic_point_histories_backup_20260729 AS
            SELECT * FROM pic_point_histories WHERE created_at < '" . self::RESET_AT . "'
        ");
        DB::statement("
            CREATE TABLE marketing_point_histories_backup_20260729 AS
            SELECT * FROM marketing_point_histories WHERE created_at < '" . self::RESET_AT . "'
        ");

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
                )
            ');

            DB::statement('
                UPDATE marketings m
                SET m.total_points = (
                    SELECT COALESCE(SUM(mph.points_earned), 0)
                    FROM marketing_point_histories mph
                    WHERE mph.marketing_id = m.id
                )
            ');

            Log::info('Hapus poin PIC/Marketing yang tidak sengaja hidup lagi setelah reset 28 Juli 2026', [
                'reset_at' => self::RESET_AT,
                'pic_rows_deleted' => $picDeleted,
                'marketing_rows_deleted' => $mktDeleted,
                'backup_tables' => ['pic_point_histories_backup_20260729', 'marketing_point_histories_backup_20260729'],
            ]);
        });
    }

    /**
     * Kembalikan baris yang dihapus dari tabel backup, lalu hitung ulang total.
     */
    public function down(): void
    {
        DB::transaction(function () {
            DB::statement('INSERT INTO pic_point_histories SELECT * FROM pic_point_histories_backup_20260729');
            DB::statement('INSERT INTO marketing_point_histories SELECT * FROM marketing_point_histories_backup_20260729');

            DB::statement('
                UPDATE pics p
                SET p.total_points = (
                    SELECT COALESCE(SUM(h.points_earned), 0)
                    FROM pic_point_histories h
                    WHERE h.pic_id = p.id
                )
            ');

            DB::statement('
                UPDATE marketings m
                SET m.total_points = (
                    SELECT COALESCE(SUM(mph.points_earned), 0)
                    FROM marketing_point_histories mph
                    WHERE mph.marketing_id = m.id
                )
            ');

            Log::info('Rollback: baris poin PIC/Marketing yang tadi dihapus (migration remove_points_resurrected_after_intentional_reset) dikembalikan dari backup');
        });
    }
};
