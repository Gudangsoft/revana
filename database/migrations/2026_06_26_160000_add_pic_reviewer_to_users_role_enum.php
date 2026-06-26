<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'reviewer', 'pic', 'pic_reviewer') NOT NULL DEFAULT 'reviewer'");
    }

    public function down(): void
    {
        // Convert any pic_reviewer back to reviewer before reverting
        DB::statement("UPDATE users SET role = 'reviewer' WHERE role = 'pic_reviewer'");
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'reviewer', 'pic') NOT NULL DEFAULT 'reviewer'");
    }
};
