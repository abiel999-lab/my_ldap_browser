<?php

namespace App\Services\Ldap;

use RuntimeException;

class LdapUnitMemberService
{
    protected string $unitsDn = 'ou=units,ou=groups,dc=petra,dc=ac,dc=id';
    protected string $peopleDn = 'ou=people,dc=petra,dc=ac,dc=id';
    protected string $dummyDn = 'uid=dummy,ou=services,dc=petra,dc=ac,dc=id';

    public function __construct(
        protected LdapNativeService $ldap,
        protected LdapUnitSyncService $unitSyncService,
    ) {
    }

    public function assignUserToUnit(string $unitCn, string $uid): array
    {
        $unitDn = "cn={$unitCn},{$this->unitsDn}";
        $userDn = "uid={$uid},{$this->peopleDn}";

        $unitEntry = $this->ldap->read($unitDn, ['cn', 'member']);

        if (! $unitEntry) {
            throw new RuntimeException("Unit {$unitCn} tidak ditemukan.");
        }

        $members = $this->normalizeDns($this->ldap->extractMany($unitEntry, 'member'));
        $userDnNorm = $this->ldap->normalizeDn($userDn);
        $dummyDnNorm = $this->ldap->normalizeDn($this->dummyDn);

        if (! in_array($userDnNorm, $members, true)) {
            if (count($members) === 1 && $members[0] === $dummyDnNorm) {
                $this->ldap->modReplace($unitDn, [
                    'member' => [$userDn],
                ]);
            } else {
                $this->ldap->modAdd($unitDn, [
                    'member' => [$userDn],
                ]);
            }
        }

        $this->unitSyncService->sync();

        return [
            'unit_dn' => $unitDn,
            'user_dn' => $userDn,
        ];
    }

    public function removeUserFromUnit(string $unitCn, string $uid): array
    {
        $unitDn = "cn={$unitCn},{$this->unitsDn}";
        $userDn = "uid={$uid},{$this->peopleDn}";

        $unitEntry = $this->ldap->read($unitDn, ['cn', 'member']);

        if (! $unitEntry) {
            throw new RuntimeException("Unit {$unitCn} tidak ditemukan.");
        }

        $members = $this->normalizeDns($this->ldap->extractMany($unitEntry, 'member'));
        $userDnNorm = $this->ldap->normalizeDn($userDn);

        if (in_array($userDnNorm, $members, true)) {
            $filtered = array_values(array_filter(
                $members,
                fn ($dn) => $dn !== $userDnNorm
            ));

            if (empty($filtered)) {
                $this->ldap->modReplace($unitDn, [
                    'member' => [$this->dummyDn],
                ]);
            } else {
                $this->ldap->modDel($unitDn, [
                    'member' => [$userDn],
                ]);
            }
        }

        $this->unitSyncService->sync();

        return [
            'unit_dn' => $unitDn,
            'user_dn' => $userDn,
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
