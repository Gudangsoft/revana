<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bug: `pic_point_histories` tidak punya batasan unik untuk (pic_id, submission_id,
     * step) di database — satu-satunya perlindungan dari poin dobel adalah pengecekan
     * "sudah ada atau belum" di level aplikasi (PicPointHistory::awardPoints()), yang
     * TIDAK atomik. Kalau 2 permintaan datang hampir bersamaan (klik ganda tombol
     * validasi, atau retry jaringan), keduanya bisa lolos pengecekan di saat yang sama
     * sebelum salah satunya sempat tersimpan — menghasilkan baris kembar (ditemukan:
     * 1.609 kelompok, 1.784 baris berlebih).
     *
     * Migration ini: (1) hapus baris kembar (simpan yang paling awal/id terkecil per
     * kelompok, hapus sisanya), (2) tambah UNIQUE INDEX (pic_id, submission_id, step)
     * supaya database sendiri yang menolak percobaan kembar ke depan, (3) hitung ulang
     * total_points PIC yang terdampak. Baris penyesuaian manual (submission_id NULL,
     * step 'adjustment') TIDAK terpengaruh — UNIQUE index tidak berlaku untuk NULL,
     * jadi admin tetap bisa memberi beberapa penyesuaian manual terpisah ke PIC yang
     * sama.
     */
    public function up(): void
    {
        $duplicateGroups = DB::table('pic_point_histories')
            ->select('pic_id', 'submission_id', 'step')
            ->whereNotNull('submission_id')
            ->groupBy('pic_id', 'submission_id', 'step')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $totalDeleted = 0;

        foreach ($duplicateGroups as $group) {
            $ids = DB::table('pic_point_histories')
                ->where('pic_id', $group->pic_id)
                ->where('submission_id', $group->submission_id)
                ->where('step', $group->step)
                ->orderBy('id')
                ->pluck('id');

            $deleteIds = $ids->slice(1); // keep the first (lowest id), delete the rest

            DB::table('pic_point_histories')->whereIn('id', $deleteIds)->delete();
            $totalDeleted += $deleteIds->count();
        }

        Schema::table('pic_point_histories', function (Blueprint $table) {
            $table->unique(['pic_id', 'submission_id', 'step'], 'pic_point_histories_unique_award');
        });

        $totalsFixed = DB::affectingStatement('
            UPDATE pics p
            LEFT JOIN (
                SELECT pic_id, COALESCE(SUM(points_earned), 0) AS actual
                FROM pic_point_histories
                GROUP BY pic_id
            ) h ON h.pic_id = p.id
            SET p.total_points = COALESCE(h.actual, 0)
            WHERE p.total_points != COALESCE(h.actual, 0)
        ');

        Log::info('Hapus data poin PIC kembar + tambah unique constraint', [
            'duplicate_groups' => $duplicateGroups->count(),
            'rows_deleted' => $totalDeleted,
            'pic_totals_recalculated' => $totalsFixed,
        ]);
    }

    public function down(): void
    {
        Schema::table('pic_point_histories', function (Blueprint $table) {
            $table->dropUnique('pic_point_histories_unique_award');
        });
        // Baris kembar yang dihapus TIDAK dikembalikan — memang seharusnya tidak ada.
    }
};
