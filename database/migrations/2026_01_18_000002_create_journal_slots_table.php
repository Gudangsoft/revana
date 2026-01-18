<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * DATA SLOT - Slot jurnal untuk publikasi
     */
    public function up(): void
    {
        Schema::create('journal_slots', function (Blueprint $table) {
            $table->id();
            $table->string('kode_slot')->unique(); // Kode slot
            $table->foreignId('journal_master_id')->constrained('journal_masters')->onDelete('cascade');
            $table->string('volume'); // Volume
            $table->string('nomor'); // Nomor
            $table->string('bulan'); // Bulan
            $table->integer('tahun'); // Tahun
            $table->integer('jumlah_slot'); // Jumlah Slot
            $table->integer('slot_terpakai')->default(0); // Slot yang sudah terpakai
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_slots');
    }
};
