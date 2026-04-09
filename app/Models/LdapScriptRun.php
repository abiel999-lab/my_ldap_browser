<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LdapScriptRun extends Model
{
    protected $fillable = [
        'script_key',
        'script_label',
        'script_path',
        'status',
        'stdout',
        'stderr',
        'exit_code',
        'actor_name',
        'actor_email',
    ];
}
