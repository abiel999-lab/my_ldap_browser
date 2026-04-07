<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LdapRoleMemberView extends Model
{
    protected $table = 'ldap_role_member_views';

    protected $fillable = [
        'role_dn',
        'role_cn',
        'uid',
        'member_dn',
        'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];
}
