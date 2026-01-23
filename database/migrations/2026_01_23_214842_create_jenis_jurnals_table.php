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
        Schema::create('jenis_jurnals', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default data
        DB::table('jenis_jurnals')->insert([
            ['name' => 'Jurnal Nasional', 'description' => 'Jurnal terakreditasi nasional', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Jurnal Internasional', 'description' => 'Jurnal terakreditasi internasional', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_jurnals');
    }
};
