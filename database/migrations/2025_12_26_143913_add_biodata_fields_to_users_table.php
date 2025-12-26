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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('institution')->nullable()->after('phone');
            $table->string('position')->nullable()->after('institution');
            $table->string('education_level')->nullable()->after('position'); // S1, S2, S3
            $table->text('specialization')->nullable()->after('education_level');
            $table->text('address')->nullable()->after('specialization');
            $table->string('nidn')->nullable()->after('address');
            $table->string('google_scholar')->nullable()->after('nidn');
            $table->string('sinta_id')->nullable()->after('google_scholar');
            $table->string('scopus_id')->nullable()->after('sinta_id');
            $table->text('bio')->nullable()->after('scopus_id');
            $table->string('photo')->nullable()->after('bio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'institution',
                'position',
                'education_level',
                'specialization',
                'address',
                'nidn',
                'google_scholar',
                'sinta_id',
                'scopus_id',
                'bio',
                'photo'
            ]);
        });
    }
};
