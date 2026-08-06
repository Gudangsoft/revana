<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Permintaan eksplisit user 31 Juli 2026 (log-update-2026-07-31.md #5): koreksi
     * riwayat tanggal validasi yang rusak akibat celah `runBulkSync()` yang baru
     * diperbaiki (COALESCE({step}_validated_at, NOW()) — jatuh ke NOW() kalau
     * validated_at kosong, membuat semua submission yang kena kondisi itu di SATU
     * eksekusi sync dapat tanggal "selesai" identik sampai ke detik, bukan tanggal
     * asli). Ditemukan lewat laporan user: halaman riwayat poin PIC menampilkan
     * banyak baris dengan jam identik utk kode submit yang berbeda-beda.
     *
     * Skala terkonfirmasi (query manual sebelum migration ini ditulis): 4.718 baris
     * submission-step (gerombolan >= 3 submission berbagi timestamp validated_at yang
     * SAMA PERSIS sampai ke detik) tersebar di 9 kolom *_validated_at, terbesar di
     * production_validated_at (2.961 baris / 202 gerombolan).
     *
     * Perbaikan: untuk tiap baris dalam gerombolan begini, {step}_validated_at
     * ditimpa dengan submissions.created_at milik baris itu SENDIRI (bukan tanggal
     * asli yang sebenarnya — itu tidak pernah tercatat & tidak bisa dipulihkan --
     * tapi jauh lebih masuk akal sebagai perkiraan dibanding "kapan sync kebetulan
     * dijalankan"). pic_point_histories.created_at/updated_at disinkronkan ulang
     * mengikuti nilai baru itu, pakai logika repair yang SAMA seperti yang sudah
     * dipercaya di runBulkSync().
     *
     * Step 'submit' TIDAK termasuk (tidak punya kolom validated_at terpisah, sudah
     * pakai submissions.created_at langsung sejak awal).
     */
    private const WORKFLOW_STEPS = [
        'editor1'    => ['field' => 'petugas_editor1_id',    'validated_at' => 'editor1_validated_at'],
        'author1'    => ['field' => 'petugas_author1_id',    'validated_at' => 'author1_validated_at'],
        'editor2'    => ['field' => 'petugas_editor2_id',    'validated_at' => 'editor2_validated_at'],
        'reviewer1'  => ['field' => 'petugas_reviewer1_id',  'validated_at' => 'reviewer1_validated_at'],
        'reviewer2'  => ['field' => 'petugas_reviewer2_id',  'validated_at' => 'reviewer2_validated_at'],
        'editor3'    => ['field' => 'petugas_editor3_id',    'validated_at' => 'editor3_validated_at'],
        'author2'    => ['field' => 'petugas_author2_id',    'validated_at' => 'author2_validated_at'],
        'production' => ['field' => 'petugas_production_id', 'validated_at' => 'production_validated_at'],
        'validator'  => ['field' => 'petugas_validator_id',  'validated_at' => 'validator_validated_at'],
    ];

    private const BATCH_THRESHOLD = 3;

    public function up(): void
    {
        // Backup dulu — DDL di luar transaksi (lihat pelajaran migration
        // 2026_07_28_000005 soal implicit commit kalau DDL dicampur transaksi manual).
        DB::statement('DROP TABLE IF EXISTS submissions_validated_at_backup_20260731');
        DB::statement('DROP TABLE IF EXISTS pic_point_histories_dates_backup_20260731');
        DB::statement('
            CREATE TABLE submissions_validated_at_backup_20260731 AS
            SELECT id, editor1_validated_at, author1_validated_at, editor2_validated_at,
                   reviewer1_validated_at, reviewer2_validated_at, editor3_validated_at,
                   author2_validated_at, production_validated_at, validator_validated_at
            FROM submissions
        ');
        DB::statement('
            CREATE TABLE pic_point_histories_dates_backup_20260731 AS
            SELECT id, created_at, updated_at FROM pic_point_histories
        ');

        DB::transaction(function () {
            $summary = [];

            foreach (self::WORKFLOW_STEPS as $step => $cfg) {
                $col   = $cfg['validated_at'];
                $field = $cfg['field'];

                $corrected = DB::affectingStatement("
                    UPDATE submissions s
                    INNER JOIN (
                        SELECT {$col} AS batch_ts
                        FROM submissions
                        WHERE {$col} IS NOT NULL
                        GROUP BY {$col}
                        HAVING COUNT(*) >= " . self::BATCH_THRESHOLD . "
                    ) b ON s.{$col} = b.batch_ts
                    SET s.{$col} = s.created_at
                ");

                $synced = DB::affectingStatement("
                    UPDATE pic_point_histories h
                    INNER JOIN submissions s ON s.id = h.submission_id AND s.{$field} = h.pic_id
                    SET h.created_at = s.{$col}, h.updated_at = s.{$col}
                    WHERE h.step = '{$step}'
                      AND s.{$col} IS NOT NULL
                      AND h.created_at <> s.{$col}
                ");

                $summary[$step] = ['submissions_corrected' => $corrected, 'histories_synced' => $synced];
            }

            Log::info('Koreksi tanggal validated_at hasil bulk-sync (NOW() fallback) atas permintaan eksplisit user (31 Juli 2026)', $summary);
        });
    }

    /**
     * Kembalikan seluruh kolom *_validated_at dan pic_point_histories.created_at/
     * updated_at persis seperti sebelum migration ini — restore penuh dari backup,
     * bukan cuma baris yang (menurut heuristik gerombolan) berubah, supaya down()
     * benar-benar mengembalikan state persis semula tanpa bergantung pada heuristik
     * yang sama.
     */
    public function down(): void
    {
        DB::transaction(function () {
            foreach (self::WORKFLOW_STEPS as $cfg) {
                $col = $cfg['validated_at'];
                DB::statement("
                    UPDATE submissions s
                    INNER JOIN submissions_validated_at_backup_20260731 b ON b.id = s.id
                    SET s.{$col} = b.{$col}
                ");
            }

            DB::statement('
                UPDATE pic_point_histories h
                INNER JOIN pic_point_histories_dates_backup_20260731 b ON b.id = h.id
                SET h.created_at = b.created_at, h.updated_at = b.updated_at
            ');

            Log::info('Rollback: koreksi tanggal validated_at (31 Juli 2026) dibatalkan, dikembalikan dari backup');
        });
    }
};
