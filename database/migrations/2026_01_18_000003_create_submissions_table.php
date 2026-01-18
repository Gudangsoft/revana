<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * DATA SUBMIT & PROSES - Workflow pengelolaan artikel jurnal
     */
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            
            // === DATA SUBMIT ===
            $table->string('kode_submit')->unique(); // Kode Submit
            $table->foreignId('journal_slot_id')->constrained('journal_slots')->onDelete('cascade');
            $table->string('id_artikel'); // Id artikel
            $table->string('judul_artikel'); // Judul artikel
            $table->text('link_artikel')->nullable(); // Link artikel
            $table->string('nama_penulis'); // Nama penulis
            $table->string('no_hp_penulis')->nullable(); // No HP penulis
            $table->string('username_author')->nullable(); // Username akses autor
            $table->string('password_author')->nullable(); // Password akses autor
            $table->string('pic_marketing')->nullable(); // PIC Marketing (isi manual)
            $table->foreignId('petugas_submit_id')->nullable()->constrained('users')->onDelete('set null'); // Petugas submit (otomatis saat Login)
            
            // === PROSES WORKFLOW ===
            
            // Petugas Editor 1: input user dan password editor dan tanya jawab ke penulis
            $table->foreignId('petugas_editor1_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('username_editor')->nullable(); // Username editor
            $table->string('password_editor')->nullable(); // Password editor
            $table->boolean('editor1_valid')->default(false); // Cek list valid
            $table->timestamp('editor1_validated_at')->nullable();
            
            // Petugas Author 1: menjawab pertanyaan dari editor1
            $table->foreignId('petugas_author1_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('author1_valid')->default(false); // Cek list valid
            $table->timestamp('author1_validated_at')->nullable();
            
            // Petugas Editor 2: input user Reviewer 1 & 2 (penugasan reviewer)
            $table->foreignId('petugas_editor2_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('editor2_valid')->default(false); // Cek list valid
            $table->timestamp('editor2_validated_at')->nullable();
            
            // Petugas Reviewer 1: menyelesaikan review (catatan dan form review)
            $table->foreignId('petugas_reviewer1_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('username_reviewer1')->nullable(); // Username Reviewer 1
            $table->string('password_reviewer1')->nullable(); // Password Reviewer 1
            $table->text('catatan_reviewer1')->nullable(); // Catatan review
            $table->boolean('reviewer1_valid')->default(false); // Cek list valid
            $table->timestamp('reviewer1_validated_at')->nullable();
            
            // Petugas Reviewer 2: menyelesaikan review (catatan dan form review)
            $table->foreignId('petugas_reviewer2_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('username_reviewer2')->nullable(); // Username Reviewer 2
            $table->string('password_reviewer2')->nullable(); // Password Reviewer 2
            $table->text('catatan_reviewer2')->nullable(); // Catatan review
            $table->boolean('reviewer2_valid')->default(false); // Cek list valid
            $table->timestamp('reviewer2_validated_at')->nullable();
            
            // Petugas Editor 3: mengirimkan ke penulis revisi
            $table->foreignId('petugas_editor3_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('editor3_valid')->default(false); // Cek list valid
            $table->timestamp('editor3_validated_at')->nullable();
            
            // Petugas Author 2: mengirimkan hasil revisi ke OJS
            $table->foreignId('petugas_author2_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('author2_valid')->default(false); // Cek list valid
            $table->timestamp('author2_validated_at')->nullable();
            
            // Petugas Production: editing dan publish
            $table->foreignId('petugas_production_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('production_valid')->default(false); // Cek list valid
            $table->timestamp('production_validated_at')->nullable();
            
            // Link Publish (hasil akhir)
            $table->text('link_publish')->nullable(); // Link Publish
            
            // Status dan tracking
            $table->enum('status', [
                'SUBMITTED',
                'EDITOR1_PROCESS',
                'AUTHOR1_PROCESS', 
                'EDITOR2_PROCESS',
                'REVIEWER1_PROCESS',
                'REVIEWER2_PROCESS',
                'EDITOR3_PROCESS',
                'AUTHOR2_PROCESS',
                'PRODUCTION_PROCESS',
                'PUBLISHED',
                'REJECTED'
            ])->default('SUBMITTED');
            
            $table->date('tanggal_submit')->nullable(); // Tanggal submit
            $table->text('notes')->nullable(); // Catatan umum
            
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Index untuk filter berdasarkan tanggal
            $table->index('tanggal_submit');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
