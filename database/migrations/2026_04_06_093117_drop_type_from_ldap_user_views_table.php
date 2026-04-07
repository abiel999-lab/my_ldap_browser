<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ldap_user_views', function (Blueprint $table) {
            if (Schema::hasColumn('ldap_user_views', 'type')) {
                $table->dropColumn('type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ldap_user_views', function (Blueprint $table) {
            if (! Schema::hasColumn('ldap_user_views', 'type')) {
                $table->string('type')->nullable()->after('mail');
            }
        });
    }
};
