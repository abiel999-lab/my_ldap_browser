<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LdapExport extends Model
{
    protected $fillable = [
        'title',
        'scope',
        'base_dn',
        'filter',
        'status',
        'total_entries',
        'ldif_path',
        'zip_path',
        'notes',
        'error_message',
        'requested_by',
    ];
}
