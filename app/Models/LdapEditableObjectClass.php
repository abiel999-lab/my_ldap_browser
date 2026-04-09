<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LdapEditableObjectClass extends Model
{
    protected $fillable = [
        'oid',
        'primary_name',
        'aliases_text',
        'description',
        'sup_text',
        'class_type',
        'obsolete',
        'must_text',
        'may_text',
        'raw_definition',
    ];

    protected $casts = [
        'obsolete' => 'boolean',
    ];
}
