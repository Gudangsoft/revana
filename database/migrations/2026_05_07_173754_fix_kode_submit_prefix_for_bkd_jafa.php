<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SUB → BKD: both 3 chars, just replace prefix
        DB::statement("
            UPDATE submissions
            SET kode_submit = CONCAT('BKD', SUBSTRING(kode_submit, 4))
            WHERE program_type = 'bkd'
              AND kode_submit LIKE 'SUB%'
        ");

        // SUB → JAFA: SUB is 3 chars, JAFA is 4 — replace prefix only
        DB::statement("
            UPDATE submissions
            SET kode_submit = CONCAT('JAFA', SUBSTRING(kode_submit, 4))
            WHERE program_type = 'jafa'
              AND kode_submit LIKE 'SUB%'
        ");
    }

    public function down(): void
    {
        // Revert BKD → SUB
        DB::statement("
            UPDATE submissions
            SET kode_submit = CONCAT('SUB', SUBSTRING(kode_submit, 4))
            WHERE program_type = 'bkd'
              AND kode_submit LIKE 'BKD%'
        ");

        // Revert JAFA → SUB
        DB::statement("
            UPDATE submissions
            SET kode_submit = CONCAT('SUB', SUBSTRING(kode_submit, 5))
            WHERE program_type = 'jafa'
              AND kode_submit LIKE 'JAFA%'
        ");
    }
};
