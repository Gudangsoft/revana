<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pics', function (Blueprint $table) {
            $table->date('tanggal_lahir')->nullable()->after('phone');
        });

        Schema::table('marketings', function (Blueprint $table) {
            $table->date('tanggal_lahir')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('pics', function (Blueprint $table) {
            $table->dropColumn('tanggal_lahir');
        });

        Schema::table('marketings', function (Blueprint $table) {
            $table->dropColumn('tanggal_lahir');
        });
    }
};
