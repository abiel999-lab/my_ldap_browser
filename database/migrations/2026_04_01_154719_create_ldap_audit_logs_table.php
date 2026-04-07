<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ldap_audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('action', 100);
            $table->text('target_dn');
            $table->text('actor')->nullable();
            $table->json('payload')->nullable();
            $table->json('result')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ldap_audit_logs');
    }
};