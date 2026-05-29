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
        Schema::create('referensi_jurnals', function (Blueprint $table) {
            $table->id();
            $table->string('nama_jurnal');
            $table->string('jenis_jurnal');
            $table->string('bidang_ilmu');
            $table->smallInteger('tahun');
            $table->text('referensi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referensi_jurnals');
    }
};
