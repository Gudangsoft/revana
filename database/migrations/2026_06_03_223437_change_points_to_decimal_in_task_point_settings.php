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
        Schema::table('task_point_settings', function (Blueprint $table) {
            // Ubah integer → decimal(8,2) agar mendukung nilai pecahan (0.1, 0.33, dst)
            $table->decimal('points', 8, 2)->default(1)->change();
        });
    }

    public function down(): void
    {
        Schema::table('task_point_settings', function (Blueprint $table) {
            $table->integer('points')->default(1)->change();
        });
    }
};
