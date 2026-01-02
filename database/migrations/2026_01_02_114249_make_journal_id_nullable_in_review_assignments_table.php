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
        Schema::table('review_assignments', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['journal_id']);
            
            // Make journal_id nullable
            $table->foreignId('journal_id')->nullable()->change();
            
            // Re-add foreign key constraint
            $table->foreign('journal_id')->references('id')->on('journals')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('review_assignments', function (Blueprint $table) {
            // Drop the nullable foreign key
            $table->dropForeign(['journal_id']);
            
            // Restore to NOT NULL
            $table->foreignId('journal_id')->change();
            
            // Re-add foreign key constraint with cascade
            $table->foreign('journal_id')->references('id')->on('journals')->onDelete('cascade');
        });
    }
};
