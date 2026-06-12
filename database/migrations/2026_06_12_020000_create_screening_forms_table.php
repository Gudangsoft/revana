<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screening_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->unique()->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('screened_by')->nullable();
            $table->json('checklist')->nullable();
            $table->decimal('similarity_score', 5, 2)->nullable();
            $table->enum('keputusan', ['diterima', 'revisi', 'ditolak'])->nullable();
            $table->text('catatan')->nullable();
            $table->string('recipient_email')->nullable();
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screening_forms');
    }
};