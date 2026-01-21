<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom point dan password untuk Marketing
     */
    public function up(): void
    {
        Schema::table('marketings', function (Blueprint $table) {
            $table->integer('total_points')->default(0)->after('is_active');
            $table->string('password')->nullable()->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketings', function (Blueprint $table) {
            $table->dropColumn(['total_points', 'password']);
        });
    }
};
