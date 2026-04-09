<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ldap_script_runs', function (Blueprint $table) {
            $table->id();
            $table->string('script_key');
            $table->string('script_label');
            $table->string('script_path');
            $table->string('status')->default('running');
            $table->longText('stdout')->nullable();
            $table->longText('stderr')->nullable();
            $table->integer('exit_code')->nullable();
            $table->string('actor_name')->nullable();
            $table->string('actor_email')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ldap_script_runs');
    }
};
