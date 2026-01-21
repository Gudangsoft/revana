<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan status *_SUBMITTED untuk workflow PIC submit pekerjaan
     */
    public function up(): void
    {
        // Ubah kolom status menjadi VARCHAR agar lebih fleksibel
        // Karena ENUM di MySQL sulit untuk di-alter
        DB::statement("ALTER TABLE submissions MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'SUBMITTED'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke ENUM (optional, bisa diabaikan)
        DB::statement("ALTER TABLE submissions MODIFY COLUMN status ENUM(
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
        ) NOT NULL DEFAULT 'SUBMITTED'");
    }
};
