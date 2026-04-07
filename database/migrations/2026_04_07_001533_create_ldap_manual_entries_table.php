<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ldap_manual_entries', function (Blueprint $table) {
            $table->id();
            $table->string('section_key')->unique();
            $table->string('title');
            $table->longText('content');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('language', 10)->default('id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ldap_manual_entries');
    }
};
