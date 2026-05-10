<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_harian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pic_id');
            $table->date('tanggal');
            $table->text('target_kerja')->nullable();
            $table->text('laporan_kinerja')->nullable();
            $table->string('bukti_hasil', 1000)->nullable();
            $table->tinyInteger('capaian_hasil')->default(0)->comment('0-100 persen');
            $table->timestamps();

            $table->foreign('pic_id')->references('id')->on('pics')->onDelete('cascade');
            $table->unique(['pic_id', 'tanggal'], 'laporan_harian_pic_tanggal_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_harian');
    }
};
