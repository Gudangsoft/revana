<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fitur baru 9 Sept 2026: admin bisa mengunggah/mengisi "Surat Tugas" untuk
 * assignment reviewer, berupa FILE (diupload) atau LINK (eksternal) —
 * kolom `assignment_letter_link` sudah ada sejak lama tapi tidak pernah bisa
 * diisi lewat UI manapun (selalu di-set null saat assignment dibuat). Kolom
 * file ditambahkan di sini supaya bisa mendukung kedua opsi sekaligus,
 * mengikuti pola `proof_file`/`proof_url` yang sudah dipakai di
 * `reward_redemptions` untuk kasus serupa (dokumen bisa file ATAU link).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('review_assignments', function (Blueprint $table) {
            $table->string('assignment_letter_file')->nullable()->after('assignment_letter_link');
        });
    }

    public function down(): void
    {
        Schema::table('review_assignments', function (Blueprint $table) {
            $table->dropColumn('assignment_letter_file');
        });
    }
};
