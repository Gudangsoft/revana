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
        Schema::table('reviewer_registrations', function (Blueprint $table) {
            $table->json('article_languages')->nullable()->after('field_of_study');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviewer_registrations', function (Blueprint $table) {
            $table->dropColumn('article_languages');
        });
    }
};
