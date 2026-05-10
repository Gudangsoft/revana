<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_harian_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('laporan_harian_id');
            $table->string('actor_type', 20); // 'pic' or 'admin'
            $table->unsignedBigInteger('actor_id');
            $table->string('actor_name', 200);
            $table->string('action', 50); // created, updated, validated, unvalidated, catatan
            $table->json('changes')->nullable(); // {field: {old, new}}
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('laporan_harian_id')->references('id')->on('laporan_harian')->onDelete('cascade');
            $table->index('laporan_harian_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_harian_logs');
    }
};
