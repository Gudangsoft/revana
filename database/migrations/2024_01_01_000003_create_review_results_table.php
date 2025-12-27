<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_assignment_id')->constrained()->onDelete('cascade');
            $table->text('file_path');
            $table->text('notes')->nullable();
            $table->text('admin_feedback')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_results');
    }
};
