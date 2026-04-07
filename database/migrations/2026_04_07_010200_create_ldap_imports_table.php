<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ldap_imports', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mode')->default('upsert');
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('total_rows')->default(0);
            $table->unsignedBigInteger('success_rows')->default(0);
            $table->unsignedBigInteger('failed_rows')->default(0);
            $table->text('notes')->nullable();
            $table->text('error_message')->nullable();
            $table->string('requested_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ldap_imports');
    }
};
