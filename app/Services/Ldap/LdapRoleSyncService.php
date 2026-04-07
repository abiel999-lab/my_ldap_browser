<?php

namespace App\Services\Ldap;

use App\Models\LdapRoleMemberView;
use App\Models\LdapRoleView;

class LdapRoleSyncService
{
    protected string $rolesDn = 'ou=roles,ou=groups,dc=petra,dc=ac,dc=id';

    public function __construct(
        protected LdapNativeService $ldap,
    ) {
    }

    public function sync(): int
    {
        $entries = $this->ldap->search(
            $this->rolesDn,
            '(objectClass=groupOfNames)',
            ['cn', 'member']
        );

        $activeRoleDns = [];
        $activePairs = [];
        $count = 0;

        for ($i = 0; $i < ($entries['count'] ?? 0); $i++) {
            $entry = $entries[$i];
            $roleDn = $this->ldap->normalizeDn($entry['dn']);
            $roleCn = $this->ldap->extractFirst($entry, 'cn') ?? 'unknown';

            $members = $this->ldap->extractMany($entry, 'member');
            $normalizedMembers = [];

            foreach ($members as $memberDn) {
                $normalizedMembers[] = $this->ldap->normalizeDn($memberDn);
            }

            $normalizedMembers = array_values(array_unique($normalizedMembers));

            LdapRoleView::updateOrCreate(
                ['dn' => $roleDn],
                [
                    'cn' => $roleCn,
                    'member_count' => count($normalizedMembers),
                    'members' => $normalizedMembers,
                    'synced_at' => now(),
                ]
            );

            $activeRoleDns[] = $roleDn;

            foreach ($normalizedMembers as $memberDn) {
                $uid = $this->extractUidFromDn($memberDn);

                LdapRoleMemberView::updateOrCreate(
                    [
                        'role_dn' => $roleDn,
                        'member_dn' => $memberDn,
                    ],
                    [
                        'role_cn' => $roleCn,
                        'uid' => $uid,
                        'synced_at' => now(),
                    ]
                );

                $activePairs[] = $roleDn . '|' . $memberDn;
            }

            $count++;
        }

        if (! empty($activeRoleDns)) {
            LdapRoleView::query()
                ->whereNotIn('dn', $activeRoleDns)
                ->delete();
        }

        $existingMembers = LdapRoleMemberView::query()->get();

        foreach ($existingMembers as $row) {
            $key = $row->role_dn . '|' . $row->member_dn;

            if (! in_array($key, $activePairs, true)) {
                $row->delete();
            }
        }

        return $count;
    }

    protected function extractUidFromDn(string $dn): ?string
    {
        preg_match('/uid=([^,]+)/i', $dn, $matches);

        return $matches[1] ?? null;
    }
}
