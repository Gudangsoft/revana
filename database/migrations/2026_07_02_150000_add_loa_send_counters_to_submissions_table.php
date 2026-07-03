<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->unsignedInteger('loa_email_sent_count')->default(0)->after('loa_sent_at');
            $table->unsignedInteger('loa_wa_sent_count')->default(0)->after('loa_email_sent_count');
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['loa_email_sent_count', 'loa_wa_sent_count']);
        });
    }
};
