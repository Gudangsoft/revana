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
        // pic_point_histories.points_earned
        Schema::table('pic_point_histories', function (Blueprint $table) {
            $table->decimal('points_earned', 8, 2)->change();
        });

        // marketing_point_histories.points_earned
        Schema::table('marketing_point_histories', function (Blueprint $table) {
            $table->decimal('points_earned', 8, 2)->change();
        });

        // pics.total_points
        Schema::table('pics', function (Blueprint $table) {
            $table->decimal('total_points', 10, 2)->default(0)->change();
        });

        // marketings.total_points
        Schema::table('marketings', function (Blueprint $table) {
            $table->decimal('total_points', 10, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('pic_point_histories', function (Blueprint $table) {
            $table->integer('points_earned')->change();
        });
        Schema::table('marketing_point_histories', function (Blueprint $table) {
            $table->integer('points_earned')->change();
        });
        Schema::table('pics', function (Blueprint $table) {
            $table->integer('total_points')->default(0)->change();
        });
        Schema::table('marketings', function (Blueprint $table) {
            $table->integer('total_points')->default(0)->change();
        });
    }
};
