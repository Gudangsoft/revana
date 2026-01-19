<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom untuk upload file artikel (Word)
     */
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->string('file_artikel')->nullable()->after('link_artikel'); // Path file artikel (Word/PDF)
            $table->string('file_artikel_original_name')->nullable()->after('file_artikel'); // Nama file asli
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['file_artikel', 'file_artikel_original_name']);
        });
    }
};
