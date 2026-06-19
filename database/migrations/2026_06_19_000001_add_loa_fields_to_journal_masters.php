<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journal_masters', function (Blueprint $table) {
            $table->string('kode_singkat', 20)->nullable()->after('nama_jurnal')->comment('Kode singkat jurnal: PAF, ISMaT, BDAS, dll');
            $table->string('e_issn', 20)->nullable()->after('kode_singkat');
            $table->string('logo_path')->nullable()->after('e_issn')->comment('Path logo jurnal di storage');
            $table->string('editor_name')->nullable()->after('logo_path');
            $table->string('editor_title')->nullable()->after('editor_name')->comment('Mis: Editor in Chief');
            $table->string('editor_signature_path')->nullable()->after('editor_title');
            $table->string('primary_color', 7)->default('#1A237E')->after('editor_signature_path');
            $table->string('secondary_color', 7)->default('#8B6914')->after('primary_color');
            $table->string('loa_kota', 100)->default('Semarang')->after('secondary_color');
            $table->date('loa_tanggal')->nullable()->after('loa_kota')->comment('Tanggal resmi LOA; jika null pakai tanggal hari ini');
        });
    }

    public function down(): void
    {
        Schema::table('journal_masters', function (Blueprint $table) {
            $table->dropColumn([
                'kode_singkat', 'e_issn', 'logo_path',
                'editor_name', 'editor_title', 'editor_signature_path',
                'primary_color', 'secondary_color', 'loa_kota', 'loa_tanggal',
            ]);
        });
    }
};
