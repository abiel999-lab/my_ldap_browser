<?php

declare(strict_types=1);

namespace App\Services\Ldap;

use App\Models\Ldap\LdapAuditLog;

class LdapAuditLogService
{
    public function log(
        string $action,
        string $targetDn,
        ?string $actor = null,
        ?array $payload = null,
        ?array $result = null
    ): void {
        LdapAuditLog::create([
            'action' => $action,
            'target_dn' => $targetDn,
            'actor' => $actor,
            'payload' => $payload,
            'result' => $result,
        ]);
    }
}