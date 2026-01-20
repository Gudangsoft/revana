<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, clear the data that references users table
        DB::table('submissions')->update([
            'petugas_submit_id' => null,
            'petugas_editor1_id' => null,
            'petugas_author1_id' => null,
            'petugas_editor2_id' => null,
            'petugas_editor3_id' => null,
            'petugas_author2_id' => null,
            'petugas_production_id' => null,
        ]);

        // Get all foreign keys on submissions table
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'submissions' 
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            AND CONSTRAINT_NAME LIKE 'submissions_petugas%'
        ");

        // Drop each foreign key if exists
        foreach ($foreignKeys as $fk) {
            DB::statement("ALTER TABLE submissions DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }

        // Add new foreign keys to pics table
        Schema::table('submissions', function (Blueprint $table) {
            $table->foreign('petugas_submit_id')->references('id')->on('pics')->onDelete('set null');
            $table->foreign('petugas_editor1_id')->references('id')->on('pics')->onDelete('set null');
            $table->foreign('petugas_author1_id')->references('id')->on('pics')->onDelete('set null');
            $table->foreign('petugas_editor2_id')->references('id')->on('pics')->onDelete('set null');
            $table->foreign('petugas_editor3_id')->references('id')->on('pics')->onDelete('set null');
            $table->foreign('petugas_author2_id')->references('id')->on('pics')->onDelete('set null');
            $table->foreign('petugas_production_id')->references('id')->on('pics')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Get all foreign keys on submissions table
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'submissions' 
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            AND CONSTRAINT_NAME LIKE 'submissions_petugas%'
        ");

        // Drop each foreign key if exists
        foreach ($foreignKeys as $fk) {
            DB::statement("ALTER TABLE submissions DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }

        Schema::table('submissions', function (Blueprint $table) {
            // Restore foreign keys to users table
            $table->foreign('petugas_submit_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('petugas_editor1_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('petugas_author1_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('petugas_editor2_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('petugas_editor3_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('petugas_author2_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('petugas_production_id')->references('id')->on('users')->onDelete('set null');
        });
    }
};
