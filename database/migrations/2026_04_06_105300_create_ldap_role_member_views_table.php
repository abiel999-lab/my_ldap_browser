<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ldap_role_member_views', function (Blueprint $table) {
            $table->id();
            $table->string('role_dn')->index();
            $table->string('role_cn')->index();
            $table->string('uid')->nullable()->index();
            $table->text('member_dn');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['role_dn', 'member_dn']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ldap_role_member_views');
    }
};
