<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ldap_audit_trails', function (Blueprint $table) {
            if (! Schema::hasColumn('ldap_audit_trails', 'ldap_status')) {
                $table->string('ldap_status')->nullable()->after('status');
            }

            if (! Schema::hasColumn('ldap_audit_trails', 'sync_status')) {
                $table->string('sync_status')->nullable()->after('ldap_status');
            }

            if (! Schema::hasColumn('ldap_audit_trails', 'error_message')) {
                $table->text('error_message')->nullable()->after('message');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ldap_audit_trails', function (Blueprint $table) {
            foreach (['ldap_status', 'sync_status', 'error_message'] as $column) {
                if (Schema::hasColumn('ldap_audit_trails', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
