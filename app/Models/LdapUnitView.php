<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LdapUnitView extends Model
{
    protected $fillable = [
        'dn',
        'cn',
        'description',
        'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];
}
