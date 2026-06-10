<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('birthday_wishes', function (Blueprint $table) {
            $table->id();
            $table->string('sender_type');   // 'admin', 'pic', 'marketing'
            $table->unsignedBigInteger('sender_id');
            $table->string('sender_name');
            $table->string('recipient_type'); // 'pic', 'marketing'
            $table->unsignedBigInteger('recipient_id');
            $table->string('recipient_name');
            $table->text('message');
            $table->unsignedSmallInteger('wish_year');
            $table->timestamps();

            $table->unique(['sender_type','sender_id','recipient_type','recipient_id','wish_year'], 'uw_one_wish_per_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('birthday_wishes');
    }
};
