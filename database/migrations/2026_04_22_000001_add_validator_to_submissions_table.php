<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan langkah Validator ke tabel submissions.
     * Validator mengecek artikel yang sudah di-publish oleh Production.
     */
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            // Petugas Validator: mengecek kesesuaian artikel yang sudah dipublish
            if (!Schema::hasColumn('submissions', 'petugas_validator_id')) {
                $table->foreignId('petugas_validator_id')->nullable()->after('production_validated_at')
                      ->constrained('pics')->onDelete('set null');
            }
            if (!Schema::hasColumn('submissions', 'validator_valid')) {
                $table->boolean('validator_valid')->default(false)->after('petugas_validator_id');
            }
            if (!Schema::hasColumn('submissions', 'validator_validated_at')) {
                $table->timestamp('validator_validated_at')->nullable()->after('validator_valid');
            }
            if (!Schema::hasColumn('submissions', 'catatan_validator')) {
                $table->text('catatan_validator')->nullable()->after('validator_validated_at');
            }
        });

        // Status is already VARCHAR(50), no need to alter enum.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropForeign(['petugas_validator_id']);
            $table->dropColumn([
                'petugas_validator_id',
                'validator_valid',
                'validator_validated_at',
                'catatan_validator',
            ]);
        });

        // Status is VARCHAR(50), no need to revert enum.
    }
};
