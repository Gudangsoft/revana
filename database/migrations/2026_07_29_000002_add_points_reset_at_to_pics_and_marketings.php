<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Perlindungan permanen terhadap insiden 29 Juli 2026 (lihat migration
     * 2026_07_29_000001 & docs/tests/log-update-2026-07-29.md #4): backfill poin
     * (`runBulkSync()` PIC & Marketing, dan `PicPointController::syncMyPoints()`)
     * tidak pernah tahu kalau seorang PIC/Marketing baru saja di-reset — jadi mereka
     * membangun ulang SEMUA riwayat lama yang sengaja dihapus, seolah itu "belum
     * tercatat". Kolom ini dicatat oleh `resetAllPoints()` setiap kali reset dijalankan,
     * lalu SEMUA jalur backfill wajib mengabaikan submission yang lebih tua dari
     * tanggal ini untuk PIC/marketing yang bersangkutan.
     */
    public function up(): void
    {
        Schema::table('pics', function (Blueprint $table) {
            $table->timestamp('points_reset_at')->nullable()->after('total_points');
        });

        Schema::table('marketings', function (Blueprint $table) {
            $table->timestamp('points_reset_at')->nullable()->after('total_points');
        });
    }

    public function down(): void
    {
        Schema::table('pics', function (Blueprint $table) {
            $table->dropColumn('points_reset_at');
        });

        Schema::table('marketings', function (Blueprint $table) {
            $table->dropColumn('points_reset_at');
        });
    }
};
