<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_harian', function (Blueprint $table) {
            $table->timestamp('validated_at')->nullable()->after('capaian_hasil');
            $table->unsignedBigInteger('validated_by')->nullable()->after('validated_at');
            $table->text('catatan_admin')->nullable()->after('validated_by');
        });
    }

    public function down(): void
    {
        Schema::table('laporan_harian', function (Blueprint $table) {
            $table->dropColumn(['validated_at', 'validated_by', 'catatan_admin']);
        });
    }
};
