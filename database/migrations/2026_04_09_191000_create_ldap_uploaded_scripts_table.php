<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ldap_uploaded_scripts')) {
            Schema::create('ldap_uploaded_scripts', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('original_filename');
                $table->string('stored_path');
                $table->string('extension')->nullable();
                $table->longText('script_content')->nullable();
                $table->boolean('is_active')->default(true);
                $table->string('uploaded_by_name')->nullable();
                $table->string('uploaded_by_email')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ldap_uploaded_scripts');
    }
};
