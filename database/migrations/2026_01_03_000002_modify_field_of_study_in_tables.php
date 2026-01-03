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
        // Modify reviewer_registrations table
        Schema::table('reviewer_registrations', function (Blueprint $table) {
            $table->foreignId('field_of_study_id')->nullable()->after('field_of_study')->constrained('field_of_studies')->onDelete('set null');
        });

        // Modify users table to add field_of_study_id for reviewers
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('field_of_study_id')->nullable()->after('specialization')->constrained('field_of_studies')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviewer_registrations', function (Blueprint $table) {
            $table->dropForeign(['field_of_study_id']);
            $table->dropColumn('field_of_study_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['field_of_study_id']);
            $table->dropColumn('field_of_study_id');
        });
    }
};
