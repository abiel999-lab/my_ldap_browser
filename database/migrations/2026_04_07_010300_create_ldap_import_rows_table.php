<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ldap_import_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ldap_import_id')->constrained('ldap_imports')->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->string('uid')->nullable();
            $table->string('dn')->nullable();
            $table->string('status')->default('pending');
            $table->text('message')->nullable();
            $table->longText('payload_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ldap_import_rows');
    }
};
