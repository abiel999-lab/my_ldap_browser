<?php

namespace App\Services\Ldap;

use App\Models\LdapAuditTrail;
use Illuminate\Support\Facades\Auth;

class LdapAuditTrailService
{
    public function log(
        string $action,
        ?string $targetUid = null,
        ?string $targetDn = null,
        ?array $beforeData = null,
        ?array $afterData = null,
        string $status = 'success',
        ?string $message = null,
        ?string $ldapStatus = null,
        ?string $syncStatus = null,
        ?string $errorMessage = null
    ): void {
        $user = Auth::user();

        LdapAuditTrail::create([
            'actor_name' => $user->name ?? 'Local Admin',
            'actor_email' => $user->email ?? null,
            'action' => $action,
            'target_uid' => $targetUid,
            'target_dn' => $targetDn,
            'before_data' => $beforeData,
            'after_data' => $afterData,
            'status' => $status,
            'ldap_status' => $ldapStatus,
            'sync_status' => $syncStatus,
            'message' => $message,
            'error_message' => $errorMessage,
        ]);
    }
}
