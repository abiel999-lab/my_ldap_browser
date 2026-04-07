<?php

declare(strict_types=1);

namespace App\Models\Ldap;

use Illuminate\Database\Eloquent\Model;

class LdapAuditLog extends Model
{
    protected $table = 'ldap_audit_logs';

    protected $fillable = [
        'action',
        'target_dn',
        'actor',
        'payload',
        'result',
    ];

    protected $casts = [
        'payload' => 'array',
        'result' => 'array',
    ];
}