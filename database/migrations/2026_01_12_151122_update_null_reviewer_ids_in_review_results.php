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
        // Update review_results that have null reviewer_id
        // Set reviewer_id to the first reviewer of the assignment
        DB::statement("
            UPDATE review_results rr
            INNER JOIN review_assignments ra ON rr.review_assignment_id = ra.id
            SET rr.reviewer_id = ra.reviewer_id
            WHERE rr.reviewer_id IS NULL AND ra.reviewer_id IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this data fix
    }
};
