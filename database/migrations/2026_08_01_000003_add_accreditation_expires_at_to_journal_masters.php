<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fitur monitoring akreditasi (1 Agustus 2026): sebelumnya masa berlaku
     * akreditasi cuma tersimpan sebagai teks bebas di `loa_status` (mis. "...sampai
     * Volume 6 Nomor 1 Tahun 2027"), tidak bisa dipakai untuk hitung mundur/
     * peringatan otomatis. Kolom ini SATU tanggal pasti kapan akreditasi jurnal
     * berakhir, diisi manual admin per jurnal (tidak bisa diambil otomatis dari
     * teks `loa_status` yang formatnya tidak konsisten antar jurnal).
     */
    public function up(): void
    {
        Schema::table('journal_masters', function (Blueprint $table) {
            $table->date('accreditation_expires_at')->nullable()->after('loa_status');
        });
    }

    public function down(): void
    {
        Schema::table('journal_masters', function (Blueprint $table) {
            $table->dropColumn('accreditation_expires_at');
        });
    }
};
