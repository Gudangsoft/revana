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
        // Modify step enum to add 'fasttrack' value
        DB::statement("ALTER TABLE submission_histories MODIFY COLUMN step ENUM(
            'submit',
            'editor1',
            'author1', 
            'editor2',
            'reviewer1',
            'reviewer2',
            'editor3',
            'author2',
            'production',
            'fasttrack'
        )");
        
        // Modify action enum to add 'created' value
        DB::statement("ALTER TABLE submission_histories MODIFY COLUMN action ENUM(
            'assigned',
            'submitted',
            'revision_request',
            'revision_submit',
            'approved',
            'rejected',
            'note_added',
            'credential_added',
            'created'
        )");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'fasttrack' from step enum
        DB::statement("ALTER TABLE submission_histories MODIFY COLUMN step ENUM(
            'submit',
            'editor1',
            'author1', 
            'editor2',
            'reviewer1',
            'reviewer2',
            'editor3',
            'author2',
            'production'
        )");
        
        // Remove 'created' from action enum
        DB::statement("ALTER TABLE submission_histories MODIFY COLUMN action ENUM(
            'assigned',
            'submitted',
            'revision_request',
            'revision_submit',
            'approved',
            'rejected',
            'note_added',
            'credential_added'
        )");
    }
};
