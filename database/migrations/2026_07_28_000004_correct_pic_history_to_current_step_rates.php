<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Koreksi retroaktif riwayat poin PIC ke rate per tahap yang berlaku SEKARANG.
     *
     * Ditemukan: rate poin PIC per tahap (task_point_settings) sudah dikonfigurasi
     * berbeda-beda sejak Mei-Juli 2026 (editor1/author1=0,1, editor2/reviewer1/reviewer2=0,2,
     * submit=0,25, validator=0,33, editor3/author2=0, production=1) — TAPI hampir seluruh
     * riwayat pic_point_histories (99%+ per tahap, dari 10 Juni sampai 27 Juli) tetap
     * menunjukkan flat 1 poin/tugas (0 untuk validator, akibat migration sebelumnya hari
     * ini yang salah asumsi). Ini bukan cuma soal migration sebelumnya — bug lama pada
     * mekanisme pemberian poin real-time membuat rate yang benar tidak pernah terpakai
     * selama lebih dari sebulan, baru benar sejak pagi ini (28 Juli 2026).
     *
     * User secara eksplisit meminta riwayat lama dikoreksi ke rate saat ini (bukan
     * dibiarkan apa adanya seperti prinsip yang dipakai untuk Marketing) — sudah
     * dikonfirmasi dampaknya: total poin PIC seluruh sistem turun ~70% (dari ~97.529
     * jadi ~28.637).
     *
     * Rate diambil DINAMIS dari task_point_settings (bukan di-hardcode) supaya migration
     * ini tetap benar kalau production punya nilai rate yang sedikit berbeda dari lokal.
     * Step 'adjustment' (penyesuaian manual admin, submission_id NULL) otomatis tidak
     * ikut karena tidak ada entry 'adjustment' di task_point_settings.
     */
    public function up(): void
    {
        DB::transaction(function () {
            $rates = DB::table('task_point_settings')
                ->where('user_type', 'pic')
                ->where('is_active', true)
                ->pluck('points', 'task_key');

            $detail = [];
            $totalFixed = 0;

            foreach ($rates as $step => $rate) {
                $fixed = DB::table('pic_point_histories')
                    ->where('step', $step)
                    ->whereNotNull('submission_id')
                    ->whereRaw('ABS(points_earned - ?) > 0.0001', [$rate])
                    ->update(['points_earned' => $rate]);

                if ($fixed > 0) {
                    $detail[$step] = ['rate' => $rate, 'rows_fixed' => $fixed];
                    $totalFixed += $fixed;
                }
            }

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

            Log::info('Koreksi retroaktif riwayat poin PIC ke rate per tahap yang berlaku sekarang', [
                'total_rows_fixed' => $totalFixed,
                'detail_per_step' => $detail,
                'pic_totals_recalculated' => $totalsFixed,
            ]);
        });
    }

    /**
     * Tidak reversible dengan tepat — nilai sebelum koreksi ini sudah diketahui tidak
     * sesuai rate yang berlaku, tidak ada gunanya dikembalikan. Lihat catatan di up().
     */
    public function down(): void
    {
        // Sengaja kosong.
    }
};
