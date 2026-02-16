<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setting_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action', 50);           // 'update', 'reset', 'import', 'export'
            $table->string('admin_name');            // Who made the change
            $table->string('admin_guard', 20)->default('web'); // Auth guard
            $table->string('setting_key')->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('batch_changes')->nullable(); // For bulk updates
            $table->timestamps();

            $table->index('action');
            $table->index('setting_key');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_audit_logs');
    }
};
