<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketings', function (Blueprint $table) {
            $table->json('additional_phones')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('marketings', function (Blueprint $table) {
            $table->dropColumn('additional_phones');
        });
    }
};
