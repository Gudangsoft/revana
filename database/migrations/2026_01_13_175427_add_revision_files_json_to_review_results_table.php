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
        Schema::table('review_results', function (Blueprint $table) {
            // Add JSON column to store multiple revision files
            $table->json('revision_files')->nullable()->after('revision_file');
            // Add column for merged PDF path
            $table->string('merged_revision_file')->nullable()->after('revision_files');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('review_results', function (Blueprint $table) {
            $table->dropColumn(['revision_files', 'merged_revision_file']);
        });
    }
};
