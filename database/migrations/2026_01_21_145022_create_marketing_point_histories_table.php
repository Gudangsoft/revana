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
        Schema::create('marketing_point_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketing_id')->constrained('marketings')->onDelete('cascade');
            $table->foreignId('submission_id')->constrained('submissions')->onDelete('cascade');
            $table->integer('points_earned');
            $table->string('description')->nullable();
            $table->timestamps();

            // Prevent duplicate points for same submission
            $table->unique(['marketing_id', 'submission_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_point_histories');
    }
};
