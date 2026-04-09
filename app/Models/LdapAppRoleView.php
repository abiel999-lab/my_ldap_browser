<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LdapAppRoleView extends Model
{
    protected $fillable = [
        'app_dn',
        'app_cn',
        'role_dn',
        'role_cn',
        'member_count',
        'members',
        'synced_at',
    ];

    protected $casts = [
        'members' => 'array',
        'synced_at' => 'datetime',
    ];
}
