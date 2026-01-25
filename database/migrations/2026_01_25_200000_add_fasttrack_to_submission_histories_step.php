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
        // Modify enum to add 'fasttrack' value
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
            'fasttrack'
        )");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'fasttrack' from enum
        DB::statement("ALTER TABLE submission_histories MODIFY COLUMN step ENUM(
            'submit',
            'editor1',
            'author1', 
            'editor2',
            'reviewer1',
            'reviewer2',
            'editor3',
            'author2',
            'production'
        )");
    }
};
