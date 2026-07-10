<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journal_masters', function (Blueprint $table) {
            $table->string('bendahara_name')->nullable()->after('editor_signature_path');
            $table->string('bendahara_signature_path')->nullable()->after('bendahara_name');
        });
    }

    public function down(): void
    {
        Schema::table('journal_masters', function (Blueprint $table) {
            $table->dropColumn(['bendahara_name', 'bendahara_signature_path']);
        });
    }
};
