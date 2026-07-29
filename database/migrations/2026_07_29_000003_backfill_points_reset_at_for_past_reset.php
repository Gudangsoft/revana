<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Menutup celah yang tersisa dari migration 2026_07_29_000001/000002: kolom
     * `points_reset_at` (penjaga backfill supaya tidak menghidupkan lagi riwayat yang
     * sengaja direset) baru ADA setelah migration 000002 — tapi reset yang SUDAH
     * TERJADI sebelumnya (28 Juli 2026 21:59:35, sebelum kolom ini ada) tidak ikut
     * tercatat di dalamnya. Akibatnya kolomnya NULL untuk semua PIC/Marketing, dan
     * `runBulkSync()` menganggap "belum pernah direset" — kalau tombol "Simpan & Sync"
     * di /admin/task-point-settings diklik lagi SEBELUM migration ini jalan, insiden
     * 29 Juli (docs/tests/log-update-2026-07-29.md #4) bisa terulang persis.
     *
     * Migration ini mengisi `points_reset_at` = momen reset yang sudah dikonfirmasi
     * (lihat #4: klaster 38 baris `pics.updated_at` identik pada waktu itu) untuk
     * SEMUA PIC & Marketing yang kolomnya masih NULL — bukan cuma yang terdampak
     * insiden, karena reset 28 Juli memang dikonfirmasi mencakup semua PIC & Marketing
     * sekaligus (bukan reset per-orang).
     */
    private const RESET_AT = '2026-07-28 21:59:35';

    public function up(): void
    {
        $picUpdated = DB::table('pics')
            ->whereNull('points_reset_at')
            ->update(['points_reset_at' => self::RESET_AT]);

        $mktUpdated = DB::table('marketings')
            ->whereNull('points_reset_at')
            ->update(['points_reset_at' => self::RESET_AT]);

        Log::info('Backfill points_reset_at untuk reset 28 Juli 2026 yang sudah terlanjur terjadi sebelum kolom ini ada', [
            'reset_at' => self::RESET_AT,
            'pic_updated' => $picUpdated,
            'marketing_updated' => $mktUpdated,
        ]);
    }

    /**
     * Tidak reversible dengan tepat secara individual (kita tidak tahu baris mana yang
     * NULL-nya "asli" vs hasil backfill ini) — tapi karena migration ini HANYA mengisi
     * baris yang tadinya NULL, mengosongkannya lagi aman (mengembalikan ke kondisi
     * sebelum migration ini, yaitu semuanya NULL kembali).
     */
    public function down(): void
    {
        DB::table('pics')->where('points_reset_at', self::RESET_AT)->update(['points_reset_at' => null]);
        DB::table('marketings')->where('points_reset_at', self::RESET_AT)->update(['points_reset_at' => null]);
    }
};
