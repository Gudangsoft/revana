<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Backfill satu kali: sebelum ini poin review dihitung berdasarkan lama hari
     * pengerjaan (skala turun 10/8/7/6/5). Kebijakan baru: flat 10 poin per review
     * selesai, berapa pun lama harinya (lihat App\Models\ReviewAssignment::awardPointsToAllReviewers()).
     * Migration ini menyesuaikan riwayat poin YANG SUDAH ADA sebelum perubahan itu
     * supaya konsisten dengan kebijakan baru, dan menambah total_points/available_points
     * reviewer terkait sebesar selisihnya.
     */
    public function up(): void
    {
        DB::transaction(function () {
            $records = DB::table('point_histories')
                ->where('type', 'EARNED')
                ->whereNotNull('review_assignment_id')
                ->where('points', '!=', 10)
                ->get();

            foreach ($records as $record) {
                $diff = 10 - $record->points;

                DB::table('point_histories')->where('id', $record->id)->update(['points' => 10]);

                DB::table('users')->where('id', $record->user_id)->increment('total_points', $diff);
                DB::table('users')->where('id', $record->user_id)->increment('available_points', $diff);
            }

            Log::info('Backfill flat 10 poin/review selesai', [
                'records_updated' => $records->count(),
                'detail' => $records->map(fn ($r) => [
                    'point_history_id' => $r->id,
                    'user_id' => $r->user_id,
                    'old_points' => $r->points,
                    'diff_added' => 10 - $r->points,
                ]),
            ]);
        });
    }

    /**
     * Tidak dibuat reversible dengan tepat — nilai poin lama per baris tidak disimpan
     * di tempat lain setelah di-backfill, jadi tidak ada cara aman untuk kembalikan
     * persis ke angka semula. Migration ini memang dimaksudkan sebagai koreksi satu
     * arah (one-way), bukan perubahan skema yang perlu di-rollback.
     */
    public function down(): void
    {
        // Sengaja kosong — lihat catatan di atas.
    }
};
