<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketing_point_histories', function (Blueprint $table) {
            // Drop foreign key constraint first, then re-add as nullable
            $table->dropForeign(['submission_id']);
            $table->unsignedBigInteger('submission_id')->nullable()->change();
            $table->foreign('submission_id')->references('id')->on('submissions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('marketing_point_histories', function (Blueprint $table) {
            $table->dropForeign(['submission_id']);
            $table->unsignedBigInteger('submission_id')->nullable(false)->change();
            $table->foreign('submission_id')->references('id')->on('submissions')->onDelete('cascade');
        });
    }
};
