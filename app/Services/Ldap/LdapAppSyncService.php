<?php

namespace App\Services\Ldap;

use App\Models\LdapAppRoleView;
use App\Models\LdapAppView;

class LdapAppSyncService
{
    protected string $appsDn = 'ou=apps,ou=groups,dc=petra,dc=ac,dc=id';

    public function __construct(
        protected LdapNativeService $ldap,
    ) {
    }

    public function sync(): int
    {
        $entries = $this->ldap->search(
            $this->appsDn,
            '(objectClass=groupOfNames)',
            ['cn', 'description', 'member']
        );

        $activeAppDns = [];
        $activeRoleDns = [];
        $count = 0;

        for ($i = 0; $i < ($entries['count'] ?? 0); $i++) {
            $entry = $entries[$i];
            $dn = $this->ldap->normalizeDn($entry['dn']);
            $cn = $this->ldap->extractFirst($entry, 'cn') ?? 'unknown';

            if (! $this->isDirectAppDn($dn)) {
                continue;
            }

            $description = $this->ldap->extractFirst($entry, 'description');
            $roles = $this->findRolesUnderApp($dn);

            LdapAppView::updateOrCreate(
                ['dn' => $dn],
                [
                    'cn' => $cn,
                    'description' => $description,
                    'role_count' => count($roles),
                    'roles' => array_map(fn ($row) => $row['role_cn'], $roles),
                    'synced_at' => now(),
                ]
            );

            $activeAppDns[] = $dn;

            foreach ($roles as $role) {
                LdapAppRoleView::updateOrCreate(
                    ['role_dn' => $role['role_dn']],
                    [
                        'app_dn' => $dn,
                        'app_cn' => $cn,
                        'role_cn' => $role['role_cn'],
                        'member_count' => $role['member_count'],
                        'members' => $role['members'],
                        'synced_at' => now(),
                    ]
                );

                $activeRoleDns[] = $role['role_dn'];
            }

            $count++;
        }

        if (! empty($activeAppDns)) {
            LdapAppView::query()->whereNotIn('dn', $activeAppDns)->delete();
        }

        if (! empty($activeRoleDns)) {
            LdapAppRoleView::query()->whereNotIn('role_dn', $activeRoleDns)->delete();
        }

        return $count;
    }

    protected function isDirectAppDn(string $dn): bool
    {
        $normalized = strtolower($dn);
        $suffix = strtolower(',' . $this->appsDn);

        if (! str_ends_with($normalized, $suffix)) {
            return false;
        }

        $left = substr($normalized, 0, -strlen($suffix));

        return substr_count($left, ',') === 0 && str_starts_with($left, 'cn=');
    }

    protected function findRolesUnderApp(string $appDn): array
    {
        $entries = $this->ldap->search(
            $appDn,
            '(objectClass=groupOfNames)',
            ['cn', 'member']
        );

        $rows = [];

        for ($i = 0; $i < ($entries['count'] ?? 0); $i++) {
            $entry = $entries[$i];
            $roleDn = $this->ldap->normalizeDn($entry['dn']);

            if ($roleDn === $this->ldap->normalizeDn($appDn)) {
                continue;
            }

            $members = array_values(array_unique(
                array_map(
                    fn ($dn) => $this->ldap->normalizeDn($dn),
                    $this->ldap->extractMany($entry, 'member')
                )
            ));

            $rows[] = [
                'role_dn' => $roleDn,
                'role_cn' => $this->ldap->extractFirst($entry, 'cn') ?? 'unknown',
                'member_count' => count($members),
                'members' => $members,
            ];
        }

        return $rows;
    }
}
