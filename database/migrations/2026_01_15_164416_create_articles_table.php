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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained('journals')->onDelete('cascade');
            $table->string('article_number')->unique();
            $table->string('title');
            
            // Author Information
            $table->string('author_name');
            $table->string('author_phone')->nullable();
            $table->string('author_username')->nullable();
            $table->string('author_password')->nullable();
            
            // Links
            $table->text('submit_link')->nullable();
            $table->text('turnitin_link')->nullable();
            $table->text('loa_link')->nullable();
            $table->text('copyediting_link')->nullable();
            $table->text('publication_link')->nullable();
            
            // PICs & Marketing
            $table->string('marketing')->nullable();
            $table->string('pic')->nullable();
            
            // Workflow Stages
            $table->string('editor1')->nullable();
            $table->string('pic_editor1')->nullable();
            $table->string('author1')->nullable();
            $table->string('pic_author1')->nullable();
            $table->string('editor2')->nullable();
            $table->string('pic_editor2')->nullable();
            
            // Reviewers
            $table->string('reviewer1')->nullable();
            $table->string('pic_reviewer1')->nullable();
            $table->string('reviewer2')->nullable();
            $table->string('pic_reviewer2')->nullable();
            
            // Copyediting & Production
            $table->string('pic_copyediting')->nullable();
            $table->string('pic_production')->nullable();
            
            // Status & Dates
            $table->enum('status', ['SUBMITTED', 'REVIEW', 'REVISION', 'COPYEDITING', 'PRODUCTION', 'PUBLISHED', 'REJECTED'])->default('SUBMITTED');
            $table->date('submission_date')->nullable();
            $table->date('review_date')->nullable();
            $table->date('revision_date')->nullable();
            $table->date('acceptance_date')->nullable();
            $table->date('publication_date')->nullable();
            
            // Notes
            $table->text('notes')->nullable();
            
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
