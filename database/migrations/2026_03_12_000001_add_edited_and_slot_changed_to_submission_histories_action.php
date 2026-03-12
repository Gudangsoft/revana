<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE submission_histories MODIFY COLUMN action ENUM(
            'assigned',
            'submitted',
            'revision_request',
            'revision_submit',
            'approved',
            'rejected',
            'note_added',
            'credential_added',
            'created',
            'edited',
            'slot_changed'
        )");
    }

    public function down(): void
    {
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
};
