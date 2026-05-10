<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop unique constraint if it still exists (check via raw query)
        try {
            Schema::table('laporan_harian', function (Blueprint $table) {
                $table->dropUnique('laporan_harian_pic_tanggal_unique');
            });
        } catch (\Exception $e) {
            // Constraint already removed — continue
        }

        // Add column only if it doesn't exist yet
        if (!Schema::hasColumn('laporan_harian', 'judul_kegiatan')) {
            Schema::table('laporan_harian', function (Blueprint $table) {
                $table->string('judul_kegiatan', 300)->nullable()->after('tanggal');
            });
        }
    }

    public function down(): void
    {
        Schema::table('laporan_harian', function (Blueprint $table) {
            if (Schema::hasColumn('laporan_harian', 'judul_kegiatan')) {
                $table->dropColumn('judul_kegiatan');
            }
            $table->unique(['pic_id', 'tanggal'], 'laporan_harian_pic_tanggal_unique');
        });
    }
};
