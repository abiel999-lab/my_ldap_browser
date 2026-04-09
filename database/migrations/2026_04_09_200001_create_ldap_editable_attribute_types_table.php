<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ldap_editable_attribute_types', function (Blueprint $table) {
            $table->id();
            $table->string('oid')->index();
            $table->string('primary_name')->index();
            $table->text('aliases_text')->nullable();
            $table->text('description')->nullable();
            $table->string('sup')->nullable();
            $table->string('equality')->nullable();
            $table->string('ordering')->nullable();
            $table->string('substr')->nullable();
            $table->string('syntax')->nullable();
            $table->string('usage')->nullable();
            $table->boolean('single_value')->default(false);
            $table->boolean('no_user_modification')->default(false);
            $table->boolean('obsolete')->default(false);
            $table->longText('raw_definition')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ldap_editable_attribute_types');
    }
};
