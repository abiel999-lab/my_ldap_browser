<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LdapRoleView extends Model
{
    protected $table = 'ldap_role_views';

    protected $fillable = [
        'dn',
        'cn',
        'member_count',
        'members',
        'synced_at',
    ];

    protected $casts = [
        'members' => 'array',
        'synced_at' => 'datetime',
    ];
}
