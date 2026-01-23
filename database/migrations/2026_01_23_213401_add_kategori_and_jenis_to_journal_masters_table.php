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
        Schema::table('journal_masters', function (Blueprint $table) {
            $table->enum('kategori', ['Penelitian', 'PKM'])->nullable()->after('accreditation');
            $table->enum('jenis_jurnal', ['Jurnal Nasional', 'Jurnal Internasional'])->nullable()->after('kategori');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_masters', function (Blueprint $table) {
            $table->dropColumn(['kategori', 'jenis_jurnal']);
        });
    }
};
