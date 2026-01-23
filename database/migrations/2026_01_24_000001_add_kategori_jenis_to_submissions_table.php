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
        Schema::table('submissions', function (Blueprint $table) {
            $table->foreignId('kategori_id')->nullable()->after('journal_slot_id')->constrained('kategoris')->onDelete('set null');
            $table->foreignId('jenis_jurnal_id')->nullable()->after('kategori_id')->constrained('jenis_jurnals')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropForeign(['kategori_id']);
            $table->dropForeign(['jenis_jurnal_id']);
            $table->dropColumn(['kategori_id', 'jenis_jurnal_id']);
        });
    }
};
