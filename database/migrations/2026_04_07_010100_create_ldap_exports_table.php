<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ldap_exports', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('scope')->default('people');
            $table->string('base_dn')->nullable();
            $table->string('filter')->default('(objectClass=*)');
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('total_entries')->default(0);
            $table->string('ldif_path')->nullable();
            $table->string('zip_path')->nullable();
            $table->text('notes')->nullable();
            $table->text('error_message')->nullable();
            $table->string('requested_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ldap_exports');
    }
};
