<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ldap_user_views', function (Blueprint $table) {
            if (! Schema::hasColumn('ldap_user_views', 'given_name')) {
                $table->string('given_name')->nullable()->after('cn');
            }

            if (! Schema::hasColumn('ldap_user_views', 'sn')) {
                $table->string('sn')->nullable()->after('given_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ldap_user_views', function (Blueprint $table) {
            if (Schema::hasColumn('ldap_user_views', 'given_name')) {
                $table->dropColumn('given_name');
            }

            if (Schema::hasColumn('ldap_user_views', 'sn')) {
                $table->dropColumn('sn');
            }
        });
    }
};
