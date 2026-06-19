<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journal_masters', function (Blueprint $table) {
            $table->boolean('loa_auto_send')->default(false)->after('loa_tanggal');
            $table->string('loa_auto_trigger', 30)->default('manual')->after('loa_auto_send')
                  ->comment('manual | production_valid | validator_valid | published');
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->timestamp('loa_sent_at')->nullable()->after('affiliation_penulis')
                  ->comment('Waktu LOA otomatis dikirim ke penulis');
        });
    }

    public function down(): void
    {
        Schema::table('journal_masters', function (Blueprint $table) {
            $table->dropColumn(['loa_auto_send', 'loa_auto_trigger']);
        });
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn('loa_sent_at');
        });
    }
};
