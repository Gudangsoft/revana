<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('point_day_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('days')->unique()->comment('Jumlah hari untuk menyelesaikan review');
            $table->integer('points')->comment('Jumlah poin yang didapat');
            $table->timestamps();
        });

        // Insert default values sesuai requirement
        DB::table('point_day_settings')->insert([
            ['days' => 1, 'points' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['days' => 2, 'points' => 8, 'created_at' => now(), 'updated_at' => now()],
            ['days' => 3, 'points' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['days' => 4, 'points' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['days' => 5, 'points' => 5, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_day_settings');
    }
};
