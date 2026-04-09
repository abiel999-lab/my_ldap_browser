<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LdapAppView extends Model
{
    protected $fillable = [
        'dn',
        'cn',
        'description',
        'role_count',
        'roles',
        'synced_at',
    ];

    protected $casts = [
        'roles' => 'array',
        'synced_at' => 'datetime',
    ];
}
