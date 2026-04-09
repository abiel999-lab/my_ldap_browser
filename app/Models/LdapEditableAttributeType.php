<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LdapEditableAttributeType extends Model
{
    protected $fillable = [
        'oid',
        'primary_name',
        'aliases_text',
        'description',
        'sup',
        'equality',
        'ordering',
        'substr',
        'syntax',
        'usage',
        'single_value',
        'no_user_modification',
        'obsolete',
        'raw_definition',
    ];

    protected $casts = [
        'single_value' => 'boolean',
        'no_user_modification' => 'boolean',
        'obsolete' => 'boolean',
    ];
}
