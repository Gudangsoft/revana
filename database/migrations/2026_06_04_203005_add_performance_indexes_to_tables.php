<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add missing database indexes for performance.
 * Based on audit: columns used in WHERE/JOIN without indexes.
 */
return new class extends Migration
{
    public function up(): void
    {
        // submissions — most queried table
        Schema::table('submissions', function (Blueprint $table) {
            // FK columns used in PIC assignment queries (orWhere per step)
            $table->index('marketing_id',          'idx_sub_marketing_id');
            $table->index('petugas_submit_id',      'idx_sub_petugas_submit');
            $table->index('petugas_editor1_id',     'idx_sub_editor1');
            $table->index('petugas_author1_id',     'idx_sub_author1');
            $table->index('petugas_editor2_id',     'idx_sub_editor2');
            $table->index('petugas_reviewer1_id',   'idx_sub_reviewer1');
            $table->index('petugas_reviewer2_id',   'idx_sub_reviewer2');
            $table->index('petugas_editor3_id',     'idx_sub_editor3');
            $table->index('petugas_author2_id',     'idx_sub_author2');
            $table->index('petugas_production_id',  'idx_sub_production');
            $table->index('petugas_validator_id',   'idx_sub_validator');
            $table->index('journal_slot_id',        'idx_sub_journal_slot');
            $table->index('process_type',           'idx_sub_process_type');
            $table->index('created_by',             'idx_sub_created_by');
        });

        // journal_slots — used in journal filtering & sync
        Schema::table('journal_slots', function (Blueprint $table) {
            $table->index('journal_master_id', 'idx_slot_journal_master');
            $table->index('is_active',         'idx_slot_is_active');
        });

        // marketing_point_histories — used in SyncController aggregation
        Schema::table('marketing_point_histories', function (Blueprint $table) {
            $table->index('marketing_id', 'idx_mktph_marketing_id');
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropIndex('idx_sub_marketing_id');
            $table->dropIndex('idx_sub_petugas_submit');
            $table->dropIndex('idx_sub_editor1');
            $table->dropIndex('idx_sub_author1');
            $table->dropIndex('idx_sub_editor2');
            $table->dropIndex('idx_sub_reviewer1');
            $table->dropIndex('idx_sub_reviewer2');
            $table->dropIndex('idx_sub_editor3');
            $table->dropIndex('idx_sub_author2');
            $table->dropIndex('idx_sub_production');
            $table->dropIndex('idx_sub_validator');
            $table->dropIndex('idx_sub_journal_slot');
            $table->dropIndex('idx_sub_process_type');
            $table->dropIndex('idx_sub_created_by');
        });

        Schema::table('journal_slots', function (Blueprint $table) {
            $table->dropIndex('idx_slot_journal_master');
            $table->dropIndex('idx_slot_is_active');
        });

        Schema::table('marketing_point_histories', function (Blueprint $table) {
            $table->dropIndex('idx_mktph_marketing_id');
        });
    }
};
