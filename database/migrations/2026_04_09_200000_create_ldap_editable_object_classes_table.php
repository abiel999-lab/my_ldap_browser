<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ldap_editable_object_classes', function (Blueprint $table) {
            $table->id();
            $table->string('oid')->index();
            $table->string('primary_name')->index();
            $table->text('aliases_text')->nullable();
            $table->text('description')->nullable();
            $table->text('sup_text')->nullable();
            $table->string('class_type')->default('STRUCTURAL');
            $table->boolean('obsolete')->default(false);
            $table->text('must_text')->nullable();
            $table->text('may_text')->nullable();
            $table->longText('raw_definition')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ldap_editable_object_classes');
    }
};
