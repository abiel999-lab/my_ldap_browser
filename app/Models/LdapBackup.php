<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LdapBackup extends Model
{
    protected $fillable = [
        'title',
        'scope',
        'base_dn',
        'status',
        'total_entries',
        'ldif_path',
        'zip_path',
        'notes',
        'error_message',
        'requested_by',
    ];
}
