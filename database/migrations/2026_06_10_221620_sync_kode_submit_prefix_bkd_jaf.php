<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            // BKD: SUBxxxxxxxx → BKDxxxxxxxx
            DB::statement("
                UPDATE submissions
                SET
                    kode_submit = CONCAT('BKD', SUBSTR(kode_submit, 4)),
                    kode_loa    = CONCAT('BKD', SUBSTR(kode_submit, 4), 'SIPERA')
                WHERE program_type = 'bkd'
                  AND kode_submit LIKE 'SUB%'
            ");

            // JAFA: SUBxxxxxxxx → JAFxxxxxxxx
            DB::statement("
                UPDATE submissions
                SET
                    kode_submit = CONCAT('JAF', SUBSTR(kode_submit, 4)),
                    kode_loa    = CONCAT('JAF', SUBSTR(kode_submit, 4), 'SIPERA')
                WHERE program_type = 'jafa'
                  AND kode_submit LIKE 'SUB%'
            ");

            // JAFA yang sudah sempat pakai prefix JAFA (4 huruf) → JAF (3 huruf)
            DB::statement("
                UPDATE submissions
                SET
                    kode_submit = CONCAT('JAF', SUBSTR(kode_submit, 5)),
                    kode_loa    = CONCAT('JAF', SUBSTR(kode_submit, 5), 'SIPERA')
                WHERE program_type = 'jafa'
                  AND kode_submit LIKE 'JAFA%'
            ");
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            // Rollback BKD → SUB
            DB::statement("
                UPDATE submissions
                SET
                    kode_submit = CONCAT('SUB', SUBSTR(kode_submit, 4)),
                    kode_loa    = CONCAT('SUB', SUBSTR(kode_submit, 4), 'SIPERA')
                WHERE program_type = 'bkd'
                  AND kode_submit LIKE 'BKD%'
            ");

            // Rollback JAF → SUB
            DB::statement("
                UPDATE submissions
                SET
                    kode_submit = CONCAT('SUB', SUBSTR(kode_submit, 4)),
                    kode_loa    = CONCAT('SUB', SUBSTR(kode_submit, 4), 'SIPERA')
                WHERE program_type = 'jafa'
                  AND kode_submit LIKE 'JAF%'
            ");
        });
    }
};
