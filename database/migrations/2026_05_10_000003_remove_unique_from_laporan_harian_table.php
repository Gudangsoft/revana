<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_harian', function (Blueprint $table) {
            $table->dropUnique('laporan_harian_pic_tanggal_unique');
            $table->string('judul_kegiatan', 300)->nullable()->after('tanggal');
        });
    }

    public function down(): void
    {
        Schema::table('laporan_harian', function (Blueprint $table) {
            $table->dropColumn('judul_kegiatan');
            $table->unique(['pic_id', 'tanggal'], 'laporan_harian_pic_tanggal_unique');
        });
    }
};
