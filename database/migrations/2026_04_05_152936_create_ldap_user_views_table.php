<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ldap_user_views', function (Blueprint $table) {
            $table->id();
            $table->string('dn')->unique();
            $table->string('uid')->nullable()->index();
            $table->string('cn')->nullable()->index();
            $table->string('mail')->nullable()->index();
            $table->string('type')->nullable()->index();
            $table->json('roles')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ldap_user_views');
    }
};
