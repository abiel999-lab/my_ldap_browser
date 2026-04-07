<?php

namespace App\Services\Ldap;

use Exception;

class LdapRoleAssignmentService
{
    protected string $baseDn = 'ou=roles,ou=groups,dc=petra,dc=ac,dc=id';
    protected string $peopleDn = 'ou=people,dc=petra,dc=ac,dc=id';
    protected string $dummyDn = 'uid=dummy,ou=services,dc=petra,dc=ac,dc=id';

    protected array $rolePriority = [
        'staff' => 4,
        'student' => 3,
        'alumni' => 2,
        'external' => 1,
    ];

    public function __construct(
        protected LdapNativeService $ldap,
    ) {
    }

    public function assignRole(string $uid, string $roleCn): array
    {
        $roleDn = "cn={$roleCn},{$this->baseDn}";
        $userDn = "uid={$uid},{$this->peopleDn}";

        $roleEntry = $this->ldap->read($roleDn, ['cn', 'member']);

        if (! $roleEntry) {
            throw new Exception("Role {$roleCn} tidak ditemukan.");
        }

        $members = $this->normalizeDns(
            $this->ldap->extractMany($roleEntry, 'member')
        );

        $userDnNorm = $this->ldap->normalizeDn($userDn);
        $dummyDnNorm = $this->ldap->normalizeDn($this->dummyDn);

        // kalau user sudah ada, tetap lanjut update affiliation biar konsisten
        if (! in_array($userDnNorm, $members, true)) {
            $this->ldap->connect();

            // jika cuma dummy, replace langsung
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
            throw new Exception("Role {$roleCn} tidak ditemukan setelah assign.");
        }

        $verifiedMembers = $this->normalizeDns(
            $this->ldap->extractMany($verified, 'member')
        );

        if (! in_array($userDnNorm, $verifiedMembers, true)) {
            throw new Exception("User {$uid} gagal ditambahkan ke role {$roleCn}.");
        }

        $affiliation = $this->recalculateUserAffiliation($uid);

        return [
            'role_dn' => $roleDn,
            'user_dn' => $userDn,
            'members' => $verifiedMembers,
            'petra_affiliation' => $affiliation,
        ];
    }

    public function removeRole(string $uid, string $roleCn): array
    {
        $roleDn = "cn={$roleCn},{$this->baseDn}";
        $userDn = "uid={$uid},{$this->peopleDn}";

        $roleEntry = $this->ldap->read($roleDn, ['cn', 'member']);

        if (! $roleEntry) {
            throw new Exception("Role {$roleCn} tidak ditemukan.");
        }

        $members = $this->normalizeDns(
            $this->ldap->extractMany($roleEntry, 'member')
        );

        $userDnNorm = $this->ldap->normalizeDn($userDn);

        if (in_array($userDnNorm, $members, true)) {
            $filtered = array_values(array_filter(
                $members,
                fn ($dn) => $dn !== $userDnNorm
            ));

            $this->ldap->connect();

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
            throw new Exception("Role {$roleCn} tidak ditemukan setelah remove.");
        }

        $verifiedMembers = $this->normalizeDns(
            $this->ldap->extractMany($verified, 'member')
        );

        if (in_array($userDnNorm, $verifiedMembers, true)) {
            throw new Exception("User {$uid} gagal dihapus dari role {$roleCn}.");
        }

        $affiliation = $this->recalculateUserAffiliation($uid);

        return [
            'role_dn' => $roleDn,
            'user_dn' => $userDn,
            'members' => $verifiedMembers,
            'petra_affiliation' => $affiliation,
        ];
    }

    protected function recalculateUserAffiliation(string $uid): string
    {
        $userDn = "uid={$uid},{$this->peopleDn}";
        $userDnNorm = $this->ldap->normalizeDn($userDn);

        $entries = $this->ldap->search(
            $this->baseDn,
            '(objectClass=groupOfNames)',
            ['cn', 'member']
        );

        $matchedRoles = [];

        for ($i = 0; $i < ($entries['count'] ?? 0); $i++) {
            $entry = $entries[$i];
            $roleCn = $this->ldap->extractFirst($entry, 'cn');

            if (! $roleCn) {
                continue;
            }

            $members = $this->normalizeDns(
                $this->ldap->extractMany($entry, 'member')
            );

            if (in_array($userDnNorm, $members, true)) {
                $mapped = $this->mapRoleCnToAffiliation($roleCn);

                if ($mapped !== null) {
                    $matchedRoles[] = $mapped;
                }
            }
        }

        $winner = $this->resolveHighestAffiliation($matchedRoles) ?? 'none';

        $this->updateUserPetraAffiliation($uid, $winner);

        return $winner;
    }

    protected function updateUserPetraAffiliation(string $uid, string $affiliation): void
    {
        $userDn = "uid={$uid},{$this->peopleDn}";

        $userEntry = $this->ldap->read($userDn, ['uid', 'petraAffiliation']);

        if (! $userEntry) {
            throw new Exception("User {$uid} tidak ditemukan saat update petraAffiliation.");
        }

        $this->ldap->connect();

        $this->ldap->modReplace($userDn, [
            'petraAffiliation' => [$affiliation],
        ]);

        $verified = $this->ldap->read($userDn, ['petraAffiliation']);

        if (! $verified) {
            throw new Exception("User {$uid} tidak ditemukan setelah update petraAffiliation.");
        }

        $current = $this->ldap->extractFirst($verified, 'petraAffiliation') ?? 'none';

        if (strtolower(trim($current)) !== strtolower(trim($affiliation))) {
            throw new Exception("petraAffiliation user {$uid} gagal diubah ke {$affiliation}.");
        }
    }

    protected function mapRoleCnToAffiliation(string $roleCn): ?string
    {
        $roleCn = strtolower(trim($roleCn));

        return match ($roleCn) {
            'role-staff', 'staff-role-web', 'staff-role-mobile' => 'staff',
            'role-student', 'student-role-web', 'student-role-mobile' => 'student',
            'role-alumni', 'alumni-role-web', 'alumni-role-mobile' => 'alumni',
            'role-external', 'external-role-web', 'external-role-mobile' => 'external',
            default => null,
        };
    }

    protected function resolveHighestAffiliation(array $roles): ?string
    {
        if (empty($roles)) {
            return null;
        }

        $roles = array_values(array_unique(array_map(
            fn ($role) => strtolower(trim((string) $role)),
            $roles
        )));

        usort($roles, function ($a, $b) {
            return ($this->rolePriority[$b] ?? 0) <=> ($this->rolePriority[$a] ?? 0);
        });

        return $roles[0] ?? null;
    }

    protected function normalizeDns(array $dns): array
    {
        $result = [];

        foreach ($dns as $dn) {
            if (! is_string($dn) || trim($dn) === '') {
                continue;
            }

            $result[] = $this->ldap->normalizeDn($dn);
        }

        return array_values(array_unique($result));
    }
}
