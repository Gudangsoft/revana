<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Revisi fitur monitoring akreditasi (1 Agustus 2026, masih hari yang sama):
     * `accreditation_expires_at` (tanggal kalender, migration 2026_08_01_000003)
     * ternyata TIDAK sesuai cara akreditasi jurnal (SINTA) sebenarnya dinyatakan.
     * Dicek langsung ke 125 data `loa_status` yang sudah terisi di database — 100%
     * konsisten memakai format periode "Volume X Nomor Y Tahun Z" (mis. "sampai
     * Volume 6 Nomor 1 Tahun 2027"), TERIKAT ke penomoran volume/terbitan jurnal
     * itu sendiri, BUKAN tanggal kalender pasti. Memaksa admin mengisi tanggal
     * kalender berarti mereka harus menerka-nerka konversi dari periode aslinya.
     *
     * Diganti ke 3 kolom terpisah (volume/nomor/tahun akhir periode akreditasi)
     * — bisa diisi admin persis apa adanya dari SK, dan `accreditation_end_tahun`
     * dipakai sebagai basis hitung mundur monitoring (satu-satunya komponen yang
     * berkorelasi dengan waktu kalender; volume/nomor cuma identitas terbitan).
     */
    public function up(): void
    {
        Schema::table('journal_masters', function (Blueprint $table) {
            $table->dropColumn('accreditation_expires_at');
        });

        Schema::table('journal_masters', function (Blueprint $table) {
            $table->unsignedInteger('accreditation_end_volume')->nullable()->after('loa_status');
            $table->unsignedInteger('accreditation_end_nomor')->nullable()->after('accreditation_end_volume');
            $table->unsignedSmallInteger('accreditation_end_tahun')->nullable()->after('accreditation_end_nomor');
        });
    }

    public function down(): void
    {
        Schema::table('journal_masters', function (Blueprint $table) {
            $table->dropColumn(['accreditation_end_volume', 'accreditation_end_nomor', 'accreditation_end_tahun']);
        });

        Schema::table('journal_masters', function (Blueprint $table) {
            $table->date('accreditation_expires_at')->nullable()->after('loa_status');
        });
    }
};
