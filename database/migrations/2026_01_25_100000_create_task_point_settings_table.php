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
        Schema::create('task_point_settings', function (Blueprint $table) {
            $table->id();
            $table->string('user_type'); // 'pic' or 'marketing'
            $table->string('task_key'); // 'editor1', 'author1', 'submit', etc.
            $table->string('task_label'); // 'Editor 1', 'Author 1', etc.
            $table->integer('points')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['user_type', 'task_key']);
        });

        // Seed default values
        $picTasks = [
            ['task_key' => 'editor1', 'task_label' => 'Editor 1', 'points' => 1],
            ['task_key' => 'author1', 'task_label' => 'Author 1', 'points' => 1],
            ['task_key' => 'editor2', 'task_label' => 'Editor 2', 'points' => 1],
            ['task_key' => 'reviewer1', 'task_label' => 'Reviewer 1', 'points' => 1],
            ['task_key' => 'reviewer2', 'task_label' => 'Reviewer 2', 'points' => 1],
            ['task_key' => 'editor3', 'task_label' => 'Editor 3', 'points' => 1],
            ['task_key' => 'author2', 'task_label' => 'Author 2', 'points' => 1],
            ['task_key' => 'production', 'task_label' => 'Production', 'points' => 1],
        ];

        $marketingTasks = [
            ['task_key' => 'submit', 'task_label' => 'Submit Artikel', 'points' => 1],
        ];

        foreach ($picTasks as $task) {
            DB::table('task_point_settings')->insert([
                'user_type' => 'pic',
                'task_key' => $task['task_key'],
                'task_label' => $task['task_label'],
                'points' => $task['points'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($marketingTasks as $task) {
            DB::table('task_point_settings')->insert([
                'user_type' => 'marketing',
                'task_key' => $task['task_key'],
                'task_label' => $task['task_label'],
                'points' => $task['points'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_point_settings');
    }
};
