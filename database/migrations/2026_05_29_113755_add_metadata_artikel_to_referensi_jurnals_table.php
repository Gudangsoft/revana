<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('referensi_jurnals', function (Blueprint $table) {
            $table->text('penulis')->nullable()->after('nama_jurnal');
            $table->text('judul_artikel')->nullable()->after('penulis');
            $table->string('volume', 20)->nullable()->after('judul_artikel');
            $table->string('nomor', 20)->nullable()->after('volume');
            $table->string('halaman', 40)->nullable()->after('nomor');
            $table->string('doi', 255)->nullable()->after('halaman');
        });
    }

    public function down(): void
    {
        Schema::table('referensi_jurnals', function (Blueprint $table) {
            $table->dropColumn(['penulis','judul_artikel','volume','nomor','halaman','doi']);
        });
    }
};
