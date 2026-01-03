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
            $table->string('article_number')->nullable()->after('article_title');
            $table->string('reviewer_username')->nullable()->after('account_password');
            $table->string('reviewer_password')->nullable()->after('reviewer_username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('review_assignments', function (Blueprint $table) {
            $table->dropColumn(['article_number', 'reviewer_username', 'reviewer_password']);
        });
    }
};
