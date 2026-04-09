<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pastikan tabel settings ada dengan kolom key dan value
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
            return;
        }

        // Tambah kolom key jika belum ada
        if (!Schema::hasColumn('settings', 'key')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->string('key')->after('id');
            });
        }

        // Tambah kolom value jika belum ada
        if (!Schema::hasColumn('settings', 'value')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->text('value')->nullable()->after('key');
            });
        }

        // Tambah unique index pada key jika belum ada
        $indexes = collect(DB::select("SHOW INDEX FROM settings WHERE Column_name = 'key'"));
        if ($indexes->isEmpty()) {
            Schema::table('settings', function (Blueprint $table) {
                $table->unique('key');
            });
        }

        // Pastikan timestamps ada
        if (!Schema::hasColumn('settings', 'created_at')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Tidak melakukan apa-apa di rollback (tabel settings dipakai oleh fitur lain)
    }
};
