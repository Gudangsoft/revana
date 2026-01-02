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
            $table->text('certificate_link')->nullable()->after('assignment_letter_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('review_assignments', function (Blueprint $table) {
            $table->dropColumn('certificate_link');
        });
    }
};
