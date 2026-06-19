<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journal_masters', function (Blueprint $table) {
            $table->string('loa_language', 5)->default('en')->after('loa_auto_trigger');
        });
    }

    public function down(): void
    {
        Schema::table('journal_masters', function (Blueprint $table) {
            $table->dropColumn('loa_language');
        });
    }
};
