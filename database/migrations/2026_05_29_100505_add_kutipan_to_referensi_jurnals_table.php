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
            $table->text('kutipan')->nullable()->after('referensi');
        });
    }

    public function down(): void
    {
        Schema::table('referensi_jurnals', function (Blueprint $table) {
            $table->dropColumn('kutipan');
        });
    }
};
