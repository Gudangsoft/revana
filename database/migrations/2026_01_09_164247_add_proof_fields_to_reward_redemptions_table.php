<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reward_redemptions', function (Blueprint $table) {
            $table->string('proof_file')->nullable()->after('admin_notes');
            $table->string('proof_url')->nullable()->after('proof_file');
            $table->text('proof_description')->nullable()->after('proof_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reward_redemptions', function (Blueprint $table) {
            $table->dropColumn(['proof_file', 'proof_url', 'proof_description']);
        });
    }
};
