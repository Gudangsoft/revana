<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel untuk menyimpan histori proses setiap tahap submission
     */
    public function up(): void
    {
        Schema::create('submission_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('submissions')->onDelete('cascade');
            
            // Tahap proses
            $table->enum('step', [
                'submit',
                'editor1',
                'author1', 
                'editor2',
                'reviewer1',
                'reviewer2',
                'editor3',
                'author2',
                'production'
            ]);
            
            // Aksi yang dilakukan
            $table->enum('action', [
                'assigned',        // Ditugaskan ke petugas
                'submitted',       // Diserahkan/dikerjakan
                'revision_request', // Minta revisi
                'revision_submit', // Kirim revisi
                'approved',        // Disetujui/valid
                'rejected',        // Ditolak
                'note_added',      // Catatan ditambahkan
                'credential_added' // Username/password ditambahkan
            ]);
            
            // Siapa yang melakukan aksi
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Catatan/komentar
            $table->text('notes')->nullable();
            
            // Data tambahan (JSON untuk fleksibilitas)
            $table->json('data')->nullable();
            
            // Nomor revisi (untuk tracking berapa kali revisi)
            $table->integer('revision_number')->default(0);
            
            $table->timestamps();
            
            // Index untuk query cepat
            $table->index(['submission_id', 'step']);
            $table->index(['submission_id', 'step', 'action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_histories');
    }
};
