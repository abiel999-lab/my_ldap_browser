<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ldap_app_views', function (Blueprint $table) {
            $table->id();
            $table->string('dn')->unique();
            $table->string('cn')->index();
            $table->string('description')->nullable();
            $table->unsignedInteger('role_count')->default(0);
            $table->json('roles')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ldap_app_views');
    }
};
