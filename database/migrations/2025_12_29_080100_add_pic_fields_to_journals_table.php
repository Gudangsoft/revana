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
        Schema::table('journals', function (Blueprint $table) {
            $table->integer('slot')->nullable()->after('id');
            $table->foreignId('pic_author_id')->nullable()->constrained('pics')->onDelete('set null')->after('created_by');
            $table->foreignId('pic_marketing_id')->nullable()->constrained('marketings')->onDelete('set null')->after('pic_author_id');
            $table->foreignId('pic_editor_id')->nullable()->constrained('pics')->onDelete('set null')->after('pic_marketing_id');
            $table->string('status')->default('PENDING')->after('points');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->dropForeign(['pic_author_id']);
            $table->dropForeign(['pic_marketing_id']);
            $table->dropForeign(['pic_editor_id']);
            $table->dropColumn(['slot', 'pic_author_id', 'pic_marketing_id', 'pic_editor_id', 'status']);
        });
    }
};
