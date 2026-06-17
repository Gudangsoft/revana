<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // If 'validator' row already exists, just delete the stale 'validasi' duplicate
        $validatorExists = DB::table('task_point_settings')
            ->where('user_type', 'pic')
            ->where('task_key', 'validator')
            ->exists();

        if ($validatorExists) {
            DB::table('task_point_settings')
                ->where('user_type', 'pic')
                ->where('task_key', 'validasi')
                ->delete();
        } else {
            // Rename 'validasi' → 'validator' so the code's step key matches
            DB::table('task_point_settings')
                ->where('user_type', 'pic')
                ->where('task_key', 'validasi')
                ->update([
                    'task_key'   => 'validator',
                    'task_label' => 'Validator',
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        DB::table('task_point_settings')
            ->where('user_type', 'pic')
            ->where('task_key', 'validator')
            ->whereNotNull('id')
            ->update([
                'task_key'   => 'validasi',
                'task_label' => 'Validasi',
                'updated_at' => now(),
            ]);
    }
};
