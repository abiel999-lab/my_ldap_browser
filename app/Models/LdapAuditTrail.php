<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LdapAuditTrail extends Model
{
    protected $table = 'ldap_audit_trails';

    protected $fillable = [
        'actor_name',
        'actor_email',
        'action',
        'target_uid',
        'target_dn',
        'before_data',
        'after_data',
        'status',
        'ldap_status',
        'sync_status',
        'message',
        'error_message',
    ];

    protected $casts = [
        'before_data' => 'array',
        'after_data' => 'array',
    ];
}
