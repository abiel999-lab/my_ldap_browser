<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LdapManualEntry extends Model
{
    protected $fillable = [
        'section_key',
        'title',
        'content',
        'sort_order',
        'is_active',
        'language',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
