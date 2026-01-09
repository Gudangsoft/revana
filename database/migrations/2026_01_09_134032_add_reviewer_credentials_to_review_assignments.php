<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('review_assignments', function (Blueprint $table) {
            // Add username and password fields for each reviewer
            $table->string('reviewer_1_username')->nullable()->after('reviewer_id');
            $table->string('reviewer_1_password')->nullable()->after('reviewer_1_username');
            
            $table->string('reviewer_2_username')->nullable()->after('reviewer_2_id');
            $table->string('reviewer_2_password')->nullable()->after('reviewer_2_username');
            
            $table->foreignId('reviewer_3_id')->nullable()->constrained('users')->onDelete('cascade')->after('reviewer_2_password');
            $table->string('reviewer_3_username')->nullable()->after('reviewer_3_id');
            $table->string('reviewer_3_password')->nullable()->after('reviewer_3_username');
            
            $table->foreignId('reviewer_4_id')->nullable()->constrained('users')->onDelete('cascade')->after('reviewer_3_password');
            $table->string('reviewer_4_username')->nullable()->after('reviewer_4_id');
            $table->string('reviewer_4_password')->nullable()->after('reviewer_4_username');
            
            $table->foreignId('reviewer_5_id')->nullable()->constrained('users')->onDelete('cascade')->after('reviewer_4_password');
            $table->string('reviewer_5_username')->nullable()->after('reviewer_5_id');
            $table->string('reviewer_5_password')->nullable()->after('reviewer_5_username');
        });
    }

    public function down(): void
    {
        Schema::table('review_assignments', function (Blueprint $table) {
            $table->dropColumn([
                'reviewer_1_username',
                'reviewer_1_password',
                'reviewer_2_username',
                'reviewer_2_password',
                'reviewer_3_username',
                'reviewer_3_password',
                'reviewer_4_username',
                'reviewer_4_password',
                'reviewer_5_username',
                'reviewer_5_password',
            ]);
            
            $table->dropForeign(['reviewer_3_id']);
            $table->dropForeign(['reviewer_4_id']);
            $table->dropForeign(['reviewer_5_id']);
            
            $table->dropColumn([
                'reviewer_3_id',
                'reviewer_4_id',
                'reviewer_5_id',
            ]);
        });
    }
};
