<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify step enum to add 'marketing' value — dipakai untuk mencatat riwayat
        // catatan_marketing sebelum ditimpa (lihat Marketing\DashboardController::updateCatatan)
        DB::statement("ALTER TABLE submission_histories MODIFY COLUMN step ENUM(
            'submit',
            'editor1',
            'author1',
            'editor2',
            'reviewer1',
            'reviewer2',
            'editor3',
            'author2',
            'production',
            'fasttrack',
            'validator',
            'marketing'
        )");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'marketing' from step enum
        DB::statement("ALTER TABLE submission_histories MODIFY COLUMN step ENUM(
            'submit',
            'editor1',
            'author1',
            'editor2',
            'reviewer1',
            'reviewer2',
            'editor3',
            'author2',
            'production',
            'fasttrack',
            'validator'
        )");
    }
};
