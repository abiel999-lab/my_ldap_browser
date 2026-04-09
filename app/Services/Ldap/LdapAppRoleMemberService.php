<?php

namespace App\Services\Ldap;

use RuntimeException;

class LdapAppRoleMemberService
{
    protected string $appsDn = 'ou=apps,ou=groups,dc=petra,dc=ac,dc=id';
    protected string $peopleDn = 'ou=people,dc=petra,dc=ac,dc=id';
    protected string $dummyDn = 'uid=dummy,ou=services,dc=petra,dc=ac,dc=id';

    public function __construct(
        protected LdapNativeService $ldap,
        protected LdapAppSyncService $syncService,
        protected LdapRoleAssignmentService $roleAssignmentService,
    ) {
    }

    public function assignUserToAppRole(string $appCn, string $roleCn, string $uid): array
    {
        $roleDn = "cn={$roleCn},cn={$appCn},{$this->appsDn}";
        $userDn = "uid={$uid},{$this->peopleDn}";

        $roleEntry = $this->ldap->read($roleDn, ['cn', 'member']);

        if (! $roleEntry) {
            throw new RuntimeException("App role {$roleCn} pada app {$appCn} tidak ditemukan.");
        }

        $members = $this->normalizeDns($this->ldap->extractMany($roleEntry, 'member'));
        $userDnNorm = $this->ldap->normalizeDn($userDn);
        $dummyDnNorm = $this->ldap->normalizeDn($this->dummyDn);

        if (! in_array($userDnNorm, $members, true)) {
            if (count($members) === 1 && $members[0] === $dummyDnNorm) {
                $this->ldap->modReplace($roleDn, [
                    'member' => [$userDn],
                ]);
            } else {
                $this->ldap->modAdd($roleDn, [
                    'member' => [$userDn],
                ]);
            }
        }

        $verified = $this->ldap->read($roleDn, ['cn', 'member']);

        if (! $verified) {
            throw new RuntimeException("App role {$roleCn} tidak ditemukan setelah assign.");
        }

        $verifiedMembers = $this->normalizeDns($this->ldap->extractMany($verified, 'member'));

        if (! in_array($userDnNorm, $verifiedMembers, true)) {
            throw new RuntimeException("User {$uid} gagal ditambahkan ke app role {$roleCn}.");
        }

        // update affiliation juga, karena app roles web/mobile ikut memengaruhi petraAffiliation
        $this->roleAssignmentService->recalculateAffiliationFromKnownUser($uid);

        $this->syncService->sync();

        return [
            'role_dn' => $roleDn,
            'user_dn' => $userDn,
            'members' => $verifiedMembers,
        ];
    }

    public function removeUserFromAppRole(string $appCn, string $roleCn, string $uid): array
    {
        $roleDn = "cn={$roleCn},cn={$appCn},{$this->appsDn}";
        $userDn = "uid={$uid},{$this->peopleDn}";

        $roleEntry = $this->ldap->read($roleDn, ['cn', 'member']);

        if (! $roleEntry) {
            throw new RuntimeException("App role {$roleCn} pada app {$appCn} tidak ditemukan.");
        }

        $members = $this->normalizeDns($this->ldap->extractMany($roleEntry, 'member'));
        $userDnNorm = $this->ldap->normalizeDn($userDn);

        if (in_array($userDnNorm, $members, true)) {
            $filtered = array_values(array_filter(
                $members,
                fn ($dn) => $dn !== $userDnNorm
            ));

            if (empty($filtered)) {
                $this->ldap->modReplace($roleDn, [
                    'member' => [$this->dummyDn],
                ]);
            } else {
                $this->ldap->modDel($roleDn, [
                    'member' => [$userDn],
                ]);
            }
        }

        $verified = $this->ldap->read($roleDn, ['cn', 'member']);

        if (! $verified) {
            throw new RuntimeException("App role {$roleCn} tidak ditemukan setelah remove.");
        }

        $verifiedMembers = $this->normalizeDns($this->ldap->extractMany($verified, 'member'));

        if (in_array($userDnNorm, $verifiedMembers, true)) {
            throw new RuntimeException("User {$uid} gagal dihapus dari app role {$roleCn}.");
        }

        $this->roleAssignmentService->recalculateAffiliationFromKnownUser($uid);

        $this->syncService->sync();

        return [
            'role_dn' => $roleDn,
            'user_dn' => $userDn,
            'members' => $verifiedMembers,
        ];
    }

    protected function normalizeDns(array $dns): array
    {
        return array_values(array_unique(array_map(
            fn ($dn) => $this->ldap->normalizeDn((string) $dn),
            array_filter($dns, fn ($dn) => filled($dn))
        )));
    }
}
