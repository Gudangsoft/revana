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
        Schema::create('reviewer_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('affiliation');
            $table->string('email')->unique();
            $table->string('scopus_id')->nullable();
            $table->string('sinta_id');
            $table->string('whatsapp');
            $table->string('field_of_study');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviewer_registrations');
    }
};
