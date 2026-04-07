<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ldap_user_views', function (Blueprint $table) {
            if (! Schema::hasColumn('ldap_user_views', 'employee_number')) {
                $table->string('employee_number')->nullable()->after('sn');
            }

            if (! Schema::hasColumn('ldap_user_views', 'user_nik')) {
                $table->string('user_nik')->nullable()->after('employee_number');
            }

            if (! Schema::hasColumn('ldap_user_views', 'petra_account_status')) {
                $table->string('petra_account_status')->nullable()->after('user_nik');
            }

            if (! Schema::hasColumn('ldap_user_views', 'petra_affiliation')) {
                $table->string('petra_affiliation')->nullable()->after('petra_account_status');
            }

            if (! Schema::hasColumn('ldap_user_views', 'student_number')) {
                $table->string('student_number')->nullable()->after('petra_affiliation');
            }

            if (! Schema::hasColumn('ldap_user_views', 'mail_alternate_address')) {
                $table->json('mail_alternate_address')->nullable()->after('student_number');
            }

            if (! Schema::hasColumn('ldap_user_views', 'petra_alternate_affiliation')) {
                $table->json('petra_alternate_affiliation')->nullable()->after('mail_alternate_address');
            }

            if (! Schema::hasColumn('ldap_user_views', 'student_number_history')) {
                $table->json('student_number_history')->nullable()->after('petra_alternate_affiliation');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ldap_user_views', function (Blueprint $table) {
            foreach ([
                'employee_number',
                'user_nik',
                'petra_account_status',
                'petra_affiliation',
                'student_number',
                'mail_alternate_address',
                'petra_alternate_affiliation',
                'student_number_history',
            ] as $column) {
                if (Schema::hasColumn('ldap_user_views', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
