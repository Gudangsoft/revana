<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journal_masters', function (Blueprint $table) {
            $table->string('accreditation_logo_path')->nullable()->after('footer_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('journal_masters', function (Blueprint $table) {
            $table->dropColumn('accreditation_logo_path');
        });
    }
};
