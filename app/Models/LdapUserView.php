<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LdapUserView extends Model
{
    protected $table = 'ldap_user_views';

    protected $fillable = [
        'dn',
        'uid',
        'cn',
        'display_name',
        'given_name',
        'sn',
        'mail',
        'employee_number',
        'user_nik',
        'petra_account_status',
        'petra_affiliation',
        'student_number',
        'mail_alternate_address',
        'petra_alternate_affiliation',
        'student_number_history',
        'roles',
        'synced_at',
    ];

    protected $casts = [
        'mail_alternate_address' => 'array',
        'petra_alternate_affiliation' => 'array',
        'student_number_history' => 'array',
        'roles' => 'array',
        'synced_at' => 'datetime',
    ];
}
