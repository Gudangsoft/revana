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
        Schema::table('articles', function (Blueprint $table) {
            // Submission Stage
            $table->boolean('submission_completed')->default(false)->after('notes');
            $table->text('submission_comment')->nullable()->after('submission_completed');
            
            // Review Stage
            $table->date('review_start_date')->nullable()->after('submission_comment');
            $table->date('review_end_date')->nullable()->after('review_start_date');
            $table->boolean('review_completed')->default(false)->after('review_end_date');
            $table->text('review_comment')->nullable()->after('review_completed');
            
            // Revision Stage
            $table->date('revision_start_date')->nullable()->after('review_comment');
            $table->date('revision_end_date')->nullable()->after('revision_start_date');
            $table->boolean('revision_completed')->default(false)->after('revision_end_date');
            $table->text('revision_comment')->nullable()->after('revision_completed');
            
            // Acceptance Stage (LOA)
            $table->boolean('acceptance_completed')->default(false)->after('revision_comment');
            $table->text('acceptance_comment')->nullable()->after('acceptance_completed');
            
            // Copyediting Stage
            $table->date('copyediting_start_date')->nullable()->after('acceptance_comment');
            $table->date('copyediting_end_date')->nullable()->after('copyediting_start_date');
            $table->boolean('copyediting_completed')->default(false)->after('copyediting_end_date');
            $table->text('copyediting_comment')->nullable()->after('copyediting_completed');
            
            // Production Stage
            $table->date('production_start_date')->nullable()->after('copyediting_comment');
            $table->date('production_end_date')->nullable()->after('production_start_date');
            $table->boolean('production_completed')->default(false)->after('production_end_date');
            $table->text('production_comment')->nullable()->after('production_completed');
            
            // Publication Stage
            $table->boolean('publication_completed')->default(false)->after('production_comment');
            $table->text('publication_comment')->nullable()->after('publication_completed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn([
                'submission_completed', 'submission_comment',
                'review_start_date', 'review_end_date', 'review_completed', 'review_comment',
                'revision_start_date', 'revision_end_date', 'revision_completed', 'revision_comment',
                'acceptance_completed', 'acceptance_comment',
                'copyediting_start_date', 'copyediting_end_date', 'copyediting_completed', 'copyediting_comment',
                'production_start_date', 'production_end_date', 'production_completed', 'production_comment',
                'publication_completed', 'publication_comment'
            ]);
        });
    }
};
