<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deadline_extension_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_assignment_id')->constrained('review_assignments')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->text('reason');
            $table->date('requested_deadline')->nullable();
            $table->string('status')->default('PENDING'); // PENDING, APPROVED, REJECTED
            $table->text('admin_note')->nullable();
            $table->foreignId('responded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            // Satu reviewer cuma boleh mengajukan SATU kali per assignment — dicek juga di
            // level aplikasi, unique index ini jaring pengaman kalau ada race condition.
            $table->unique(['review_assignment_id', 'reviewer_id'], 'der_assignment_reviewer_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deadline_extension_requests');
    }
};
