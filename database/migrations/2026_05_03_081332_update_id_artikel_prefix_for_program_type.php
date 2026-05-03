<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // BKD: tambah prefix BKD- jika belum ada
        DB::table('submissions')
            ->where('program_type', 'bkd')
            ->whereNotNull('id_artikel')
            ->where('id_artikel', 'not like', 'BKD-%')
            ->update([
                'id_artikel' => DB::raw("CONCAT('BKD-', id_artikel)"),
            ]);

        // JAFA: tambah prefix JAFA- jika belum ada
        DB::table('submissions')
            ->where('program_type', 'jafa')
            ->whereNotNull('id_artikel')
            ->where('id_artikel', 'not like', 'JAFA-%')
            ->update([
                'id_artikel' => DB::raw("CONCAT('JAFA-', id_artikel)"),
            ]);
    }

    public function down(): void
    {
        // Hapus prefix BKD- dari data BKD
        DB::table('submissions')
            ->where('program_type', 'bkd')
            ->where('id_artikel', 'like', 'BKD-%')
            ->update([
                'id_artikel' => DB::raw("SUBSTRING(id_artikel, 5)"),
            ]);

        // Hapus prefix JAFA- dari data JAFA
        DB::table('submissions')
            ->where('program_type', 'jafa')
            ->where('id_artikel', 'like', 'JAFA-%')
            ->update([
                'id_artikel' => DB::raw("SUBSTRING(id_artikel, 6)"),
            ]);
    }
};
