<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ldap_audit_trails', function (Blueprint $table) {
            $table->id();
            $table->string('actor_name')->nullable();
            $table->string('actor_email')->nullable();
            $table->string('action')->index();
            $table->string('target_uid')->nullable()->index();
            $table->text('target_dn')->nullable();
            $table->json('before_data')->nullable();
            $table->json('after_data')->nullable();
            $table->string('status')->default('success');
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ldap_audit_trails');
    }
};
