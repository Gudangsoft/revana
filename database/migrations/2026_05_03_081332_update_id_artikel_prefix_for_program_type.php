<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── BKD ──────────────────────────────────────────────────────
        // id_artikel
        DB::table('submissions')
            ->where('program_type', 'bkd')
            ->whereNotNull('id_artikel')
            ->where('id_artikel', 'not like', 'BKD-%')
            ->update(['id_artikel' => DB::raw("CONCAT('BKD-', id_artikel)")]);

        // kode_submit
        DB::table('submissions')
            ->where('program_type', 'bkd')
            ->whereNotNull('kode_submit')
            ->where('kode_submit', 'not like', 'BKD-%')
            ->update(['kode_submit' => DB::raw("CONCAT('BKD-', kode_submit)")]);

        // kode_loa — rebuild dari kode_submit yang sudah di-prefix
        DB::table('submissions')
            ->where('program_type', 'bkd')
            ->whereNotNull('kode_loa')
            ->where('kode_loa', 'not like', 'BKD-%')
            ->update(['kode_loa' => DB::raw("CONCAT('BKD-', kode_loa)")]);

        // ── JAFA ─────────────────────────────────────────────────────
        // id_artikel
        DB::table('submissions')
            ->where('program_type', 'jafa')
            ->whereNotNull('id_artikel')
            ->where('id_artikel', 'not like', 'JAFA-%')
            ->update(['id_artikel' => DB::raw("CONCAT('JAFA-', id_artikel)")]);

        // kode_submit
        DB::table('submissions')
            ->where('program_type', 'jafa')
            ->whereNotNull('kode_submit')
            ->where('kode_submit', 'not like', 'JAFA-%')
            ->update(['kode_submit' => DB::raw("CONCAT('JAFA-', kode_submit)")]);

        // kode_loa
        DB::table('submissions')
            ->where('program_type', 'jafa')
            ->whereNotNull('kode_loa')
            ->where('kode_loa', 'not like', 'JAFA-%')
            ->update(['kode_loa' => DB::raw("CONCAT('JAFA-', kode_loa)")]);
    }

    public function down(): void
    {
        // BKD — hapus prefix
        DB::table('submissions')
            ->where('program_type', 'bkd')
            ->where('id_artikel', 'like', 'BKD-%')
            ->update(['id_artikel' => DB::raw("SUBSTRING(id_artikel, 5)")]);

        DB::table('submissions')
            ->where('program_type', 'bkd')
            ->where('kode_submit', 'like', 'BKD-%')
            ->update(['kode_submit' => DB::raw("SUBSTRING(kode_submit, 5)")]);

        DB::table('submissions')
            ->where('program_type', 'bkd')
            ->where('kode_loa', 'like', 'BKD-%')
            ->update(['kode_loa' => DB::raw("SUBSTRING(kode_loa, 5)")]);

        // JAFA — hapus prefix (JAFA- = 5 karakter)
        DB::table('submissions')
            ->where('program_type', 'jafa')
            ->where('id_artikel', 'like', 'JAFA-%')
            ->update(['id_artikel' => DB::raw("SUBSTRING(id_artikel, 6)")]);

        DB::table('submissions')
            ->where('program_type', 'jafa')
            ->where('kode_submit', 'like', 'JAFA-%')
            ->update(['kode_submit' => DB::raw("SUBSTRING(kode_submit, 6)")]);

        DB::table('submissions')
            ->where('program_type', 'jafa')
            ->where('kode_loa', 'like', 'JAFA-%')
            ->update(['kode_loa' => DB::raw("SUBSTRING(kode_loa, 6)")]);
    }
};
