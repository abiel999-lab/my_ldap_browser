<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LdapSchemaEntry extends Model
{
    protected $table = 'ldap_schema_entries';

    public $timestamps = false;

    protected $guarded = [];

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $attributes = [
        'id' => '',
        'type' => '',
        'name' => '',
        'oid' => '',
        'description' => '',
        'sup' => '',
        'must' => '[]',
        'may' => '[]',
        'raw' => '',
    ];

    protected $casts = [
        'must' => 'array',
        'may' => 'array',
    ];
}
