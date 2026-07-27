<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Bug: beberapa jalur (MarketingPointHistory::awardPoints(), Marketing::syncPoints(),
     * tombol Sinkron di /admin/marketing-points & /admin/sync) menghitung ulang
     * marketings.total_points dari COUNT submission (asumsi "1 submission = 1 poin"
     * SELALU berlaku). Tapi rate poin per submission bisa berubah dari waktu ke waktu
     * (lihat TaskPointSetting) — begitu rate pernah berubah, COUNT tidak lagi cocok
     * dengan SUM riwayat poin yang sebenarnya pernah diberikan, dan jalur-jalur di atas
     * akan diam-diam menimpa total_points yang benar dengan angka yang lebih rendah
     * (Marketing::syncPoints() bahkan otomatis jalan setiap marketing buka halaman
     * poinnya sendiri). Migration ini mengoreksi total_points SEKARANG dari SUM riwayat
     * yang sebenarnya, supaya production langsung benar begitu deploy, tidak perlu
     * menunggu admin ingat untuk klik tombol Sinkron.
     */
    public function up(): void
    {
        DB::transaction(function () {
            $corrected = DB::select("
                SELECT m.id, m.total_points as old_points, COALESCE(SUM(mph.points_earned), 0) as actual_points
                FROM marketings m
                LEFT JOIN marketing_point_histories mph ON mph.marketing_id = m.id
                GROUP BY m.id, m.total_points
                HAVING ROUND(m.total_points, 4) != ROUND(COALESCE(SUM(mph.points_earned), 0), 4)
            ");

            foreach ($corrected as $row) {
                DB::table('marketings')->where('id', $row->id)->update(['total_points' => $row->actual_points]);
            }

            Log::info('Fix marketing total_points: COUNT submission -> SUM riwayat poin', [
                'marketings_corrected' => count($corrected),
                'detail' => array_map(fn ($r) => [
                    'marketing_id' => $r->id,
                    'old_points' => $r->old_points,
                    'actual_points' => $r->actual_points,
                ], $corrected),
            ]);
        });
    }

    /**
     * Tidak reversible dengan tepat — nilai lama (COUNT-based) sudah salah by design,
     * tidak ada gunanya dikembalikan. Lihat catatan di up().
     */
    public function down(): void
    {
        // Sengaja kosong — lihat catatan di atas.
    }
};
