<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Toggle opsional: tampilkan/sembunyikan tanda tangan & nama editor pada
     * dokumen LOA. Default true supaya jurnal yang sudah punya editor_name/
     * editor_signature_path terisi tetap tampil seperti sebelumnya setelah
     * migration ini — toggle ini murni override tambahan, bukan pengganti
     * kondisi "isi data" yang sudah ada.
     */
    public function up(): void
    {
        Schema::table('journal_masters', function (Blueprint $table) {
            $table->boolean('loa_show_signature')->default(true)->after('editor_signature_path');
        });
    }

    public function down(): void
    {
        Schema::table('journal_masters', function (Blueprint $table) {
            $table->dropColumn('loa_show_signature');
        });
    }
};
