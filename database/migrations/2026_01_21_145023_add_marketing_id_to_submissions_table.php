<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan foreign key marketing_id ke submissions
     */
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->foreignId('marketing_id')->nullable()->after('pic_marketing')->constrained('marketings')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropForeign(['marketing_id']);
            $table->dropColumn('marketing_id');
        });
    }
};
