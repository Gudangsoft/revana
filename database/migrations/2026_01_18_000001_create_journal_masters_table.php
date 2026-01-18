<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * DATA JURNAL - Master data jurnal
     */
    public function up(): void
    {
        Schema::create('journal_masters', function (Blueprint $table) {
            $table->id();
            $table->string('kode_jurnal')->unique(); // Kode Jurnal
            $table->string('nama_jurnal'); // Nama Jurnal
            $table->string('publisher'); // Publisher
            $table->text('link_jurnal'); // Link jurnal
            $table->string('accreditation')->nullable(); // SINTA level
            $table->integer('points')->default(0);
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
        Schema::dropIfExists('journal_masters');
    }
};
