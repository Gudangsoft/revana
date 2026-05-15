<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();

            // Identitas institusi
            $table->string('name');
            $table->string('institution')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            // Akses & domain
            $table->string('subdomain')->unique();        // univ-a → univ-a.apji.org
            $table->string('custom_domain')->nullable();  // domain sendiri (opsional)

            // Database tenant
            $table->string('db_name')->unique();          // tenant_univ_a
            $table->string('db_user')->nullable();
            $table->string('db_password')->nullable();

            // Fitur (JSON: {"sms_gateway":true,"fasttrack":false,...})
            $table->json('features')->nullable();

            // Paket & status
            $table->string('plan')->default('trial');                              // trial, basic, pro, enterprise
            $table->enum('status', ['active', 'trial', 'suspended', 'expired'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            // Admin default tenant
            $table->string('admin_name')->nullable();
            $table->string('admin_email')->nullable();

            // Catatan internal
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
