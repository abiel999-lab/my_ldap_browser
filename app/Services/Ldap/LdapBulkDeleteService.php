<?php

namespace App\Services\Ldap;

use App\Services\Oidc\OidcSessionAuthenticator;
use Illuminate\Support\Facades\Auth;

class LdapBulkDeleteService
{
    public function __construct(
        protected LdapAuditTrailService $auditTrailService,
        protected OidcSessionAuthenticator $oidcSessionAuthenticator,
    ) {
    }

    public function guardAgainstDeletingCurrentUser(?string $uid, ?string $email = null): void
    {
        $authUser = Auth::user();
        $oidcUser = $this->oidcSessionAuthenticator->user();

        $currentEmail = strtolower(trim((string) ($authUser->email ?? $oidcUser['email'] ?? '')));
        $currentUidCandidates = array_filter([
            strtolower(trim((string) ($uid ?? ''))),
            strtolower(trim((string) ($email ?? ''))),
        ]);

        if ($currentEmail !== '' && in_array($currentEmail, $currentUidCandidates, true)) {
            throw new \RuntimeException('User yang sedang login tidak boleh dihapus.');
        }
    }

    public function logSuccess(
        string $action,
        ?string $targetUid,
        ?string $targetDn,
        ?array $beforeData = null,
        ?array $afterData = null,
        string $message = 'Success',
        string $ldapStatus = 'success',
        string $syncStatus = 'not_run',
    ): void {
        $this->auditTrailService->log(
            action: $action,
            targetUid: $targetUid,
            targetDn: $targetDn,
            beforeData: $beforeData,
            afterData: $afterData,
            status: 'success',
            ldapStatus: $ldapStatus,
            syncStatus: $syncStatus,
            message: $message,
            errorMessage: null,
        );
    }

    public function logFailure(
        string $action,
        ?string $targetUid,
        ?string $targetDn,
        ?array $beforeData = null,
        ?array $afterData = null,
        string $message = 'Failed',
        string $ldapStatus = 'failed',
        string $syncStatus = 'not_run',
        ?string $errorMessage = null,
    ): void {
        $this->auditTrailService->log(
            action: $action,
            targetUid: $targetUid,
            targetDn: $targetDn,
            beforeData: $beforeData,
            afterData: $afterData,
            status: 'failed',
            ldapStatus: $ldapStatus,
            syncStatus: $syncStatus,
            message: $message,
            errorMessage: $errorMessage,
        );
    }
}
