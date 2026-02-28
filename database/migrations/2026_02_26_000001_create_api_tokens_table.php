<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // nama aplikasi pemilik token
            $table->string('app_identifier')->unique();     // identifikasi unik app (slug)
            $table->string('token', 80)->unique();           // token rahasia hashed
            $table->string('token_plain')->nullable();       // hanya tampil sekali saat dibuat
            $table->json('permissions')->nullable();         // akses yang diizinkan: ['journals','accreditations',...]
            $table->string('allowed_ips')->nullable();       // pembatasan IP (opsional, comma separated)
            $table->unsignedBigInteger('rate_limit')->default(60); // request per menit
            $table->timestamp('expires_at')->nullable();    // null = tidak kedaluwarsa
            $table->timestamp('last_used_at')->nullable();
            $table->unsignedBigInteger('total_requests')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();               // catatan penggunaan token
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_tokens');
    }
};
