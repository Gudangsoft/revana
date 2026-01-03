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
        // Add new settings for point configuration
        DB::table('settings')->insert([
            [
                'key' => 'point_value',
                'value' => '1000',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'points_per_review',
                'value' => '5',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->whereIn('key', ['point_value', 'points_per_review'])->delete();
    }
};
