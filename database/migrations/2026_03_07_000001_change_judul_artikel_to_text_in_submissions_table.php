<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Fix for: SQLSTATE[22001]: String data, right truncated: 1406 Data too long for column 'judul_artikel'
     */
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->text('judul_artikel')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->string('judul_artikel')->change();
        });
    }
};
