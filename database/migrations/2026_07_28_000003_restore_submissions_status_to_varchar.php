<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * `submissions.status` seharusnya sudah VARCHAR(50) sejak migration
     * 2026_01_21_000001_add_submitted_status_to_submissions.php (dibuat khusus supaya
     * status *_SUBMITTED bisa dipakai bebas tanpa perlu ALTER ENUM setiap kali ada step
     * baru — komentar migration itu sendiri bilang "karena ENUM di MySQL sulit untuk
     * di-alter"). Tapi entah bagaimana (kemungkinan ALTER TABLE manual di luar sistem
     * migration, bukan lewat migration file manapun — tidak ditemukan migration lain
     * yang mengubahnya balik), kolom ini balik jadi ENUM tanpa nilai *_SUBMITTED sama
     * sekali, menyebabkan setiap PIC yang submit pekerjaan di tahap manapun
     * (EDITOR1_SUBMITTED, PRODUCTION_SUBMITTED, dst.) gagal dengan error
     * "Data truncated for column 'status'".
     *
     * Migration ini mengembalikan ke VARCHAR(50) — aman dijalankan berkali-kali
     * (idempoten), dan tidak kehilangan data karena isi kolom tidak diubah, cuma tipe
     * datanya saja.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE submissions MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'SUBMITTED'");
    }

    /**
     * Sengaja TIDAK dikembalikan ke ENUM — itu justru akar masalah yang diperbaiki di
     * sini. Lihat catatan di up().
     */
    public function down(): void
    {
        // Sengaja kosong.
    }
};
