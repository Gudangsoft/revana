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
            $table->string('article_title')->nullable()->after('journal_id');
            $table->text('submit_link')->nullable()->after('article_title');
            $table->string('account_username')->nullable()->after('submit_link');
            $table->string('account_password')->nullable()->after('account_username');
            $table->text('assignment_letter_link')->nullable()->after('account_password');
            $table->date('deadline')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('review_assignments', function (Blueprint $table) {
            $table->dropColumn([
                'article_title',
                'submit_link',
                'account_username',
                'account_password',
                'assignment_letter_link',
                'deadline'
            ]);
        });
    }
};
