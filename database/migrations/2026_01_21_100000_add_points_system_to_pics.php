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
        // Add total_points column to pics table
        Schema::table('pics', function (Blueprint $table) {
            $table->integer('total_points')->default(0)->after('is_active');
        });

        // Create pic_point_histories table
        Schema::create('pic_point_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pic_id')->constrained('pics')->onDelete('cascade');
            $table->foreignId('submission_id')->nullable()->constrained('submissions')->onDelete('set null');
            $table->string('step', 50); // editor1, author1, editor2, editor3, author2, production
            $table->integer('points_earned');
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['pic_id', 'created_at']);
            $table->index(['submission_id', 'step']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pic_point_histories');

        Schema::table('pics', function (Blueprint $table) {
            $table->dropColumn('total_points');
        });
    }
};
