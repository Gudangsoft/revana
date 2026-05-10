<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $uniqueExists = collect(DB::select(
            "SHOW INDEX FROM laporan_harian WHERE Key_name = 'laporan_harian_pic_tanggal_unique'"
        ))->isNotEmpty();

        if ($uniqueExists) {
            // Drop FK first so we can drop the unique index
            try {
                DB::statement('ALTER TABLE laporan_harian DROP FOREIGN KEY laporan_harian_pic_id_foreign');
            } catch (\Exception $e) {}

            // Add plain index on pic_id to back the FK
            $picIdIndexExists = collect(DB::select(
                "SHOW INDEX FROM laporan_harian WHERE Key_name = 'idx_lh_pic_id'"
            ))->isNotEmpty();
            if (!$picIdIndexExists) {
                DB::statement('ALTER TABLE laporan_harian ADD INDEX idx_lh_pic_id (pic_id)');
            }

            // Drop the unique constraint
            DB::statement('ALTER TABLE laporan_harian DROP INDEX laporan_harian_pic_tanggal_unique');

            // Re-add FK
            DB::statement('ALTER TABLE laporan_harian ADD CONSTRAINT laporan_harian_pic_id_foreign FOREIGN KEY (pic_id) REFERENCES pics(id) ON DELETE CASCADE');
        }

        // Add judul_kegiatan column if missing
        if (!Schema::hasColumn('laporan_harian', 'judul_kegiatan')) {
            DB::statement('ALTER TABLE laporan_harian ADD COLUMN judul_kegiatan VARCHAR(300) NULL AFTER tanggal');
        }
    }

    public function down(): void
    {
        //
    }
};
