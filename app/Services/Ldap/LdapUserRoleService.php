<?php

namespace App\Services\Ldap;

class LdapUserRoleService
{
    public function __construct(
        protected LdapNativeService $ldap,
        protected LdapRoleSyncService $syncService,
    ) {
    }

    public function deleteByDn(string $dn): void
    {
        $this->ldap->delete($dn);
        $this->syncService->sync();
    }
}
