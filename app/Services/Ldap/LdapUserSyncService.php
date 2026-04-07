<?php

namespace App\Services\Ldap;

use App\Models\LdapUserView;

class LdapUserSyncService
{
    protected string $peopleDn = 'ou=people,dc=petra,dc=ac,dc=id';
    protected string $rolesDn = 'ou=roles,ou=groups,dc=petra,dc=ac,dc=id';

    public function __construct(
        protected LdapNativeService $ldap,
    ) {
    }

    public function sync(): int
    {
        $entries = $this->ldap->search(
            $this->peopleDn,
            '(objectClass=inetOrgPerson)',
            [
                'uid',
                'cn',
                'displayName',
                'givenName',
                'sn',
                'mail',
                'employeeNumber',
                'userNIK',
                'petraAccountStatus',
                'studentNumber',
                'mailAlternateAddress',
                'studentNumberHistory',
            ]
        );

        $roleMap = $this->buildRoleMap();

        $activeDns = [];
        $count = 0;

        for ($i = 0; $i < ($entries['count'] ?? 0); $i++) {
            $entry = $entries[$i];
            $dn = $this->ldap->normalizeDn($entry['dn']);

            LdapUserView::updateOrCreate(
                ['dn' => $dn],
                [
                    'uid' => $this->ldap->extractFirst($entry, 'uid'),
                    'cn' => $this->ldap->extractFirst($entry, 'cn'),
                    'display_name' => $this->ldap->extractFirst($entry, 'displayName'),
                    'given_name' => $this->ldap->extractFirst($entry, 'givenName'),
                    'sn' => $this->ldap->extractFirst($entry, 'sn'),
                    'mail' => $this->ldap->extractFirst($entry, 'mail'),
                    'employee_number' => $this->ldap->extractFirst($entry, 'employeeNumber'),
                    'user_nik' => $this->ldap->extractFirst($entry, 'userNIK'),
                    'petra_account_status' => $this->ldap->extractFirst($entry, 'petraAccountStatus'),
                    'student_number' => $this->ldap->extractFirst($entry, 'studentNumber'),
                    'mail_alternate_address' => $this->ldap->extractMany($entry, 'mailAlternateAddress'),
                    'student_number_history' => $this->ldap->extractMany($entry, 'studentNumberHistory'),
                    'roles' => $roleMap[$dn] ?? [],
                    'synced_at' => now(),
                ]
            );

            $activeDns[] = $dn;
            $count++;
        }

        if (! empty($activeDns)) {
            LdapUserView::query()
                ->whereNotIn('dn', $activeDns)
                ->delete();
        }

        return $count;
    }

    protected function buildRoleMap(): array
    {
        $entries = $this->ldap->search(
            $this->rolesDn,
            '(objectClass=groupOfNames)',
            ['cn', 'member']
        );

        $map = [];

        for ($i = 0; $i < ($entries['count'] ?? 0); $i++) {
            $entry = $entries[$i];
            $cn = $this->ldap->extractFirst($entry, 'cn');

            if (! $cn) {
                continue;
            }

            $members = $this->ldap->extractMany($entry, 'member');

            foreach ($members as $memberDn) {
                $memberDn = $this->ldap->normalizeDn($memberDn);

                if (! isset($map[$memberDn])) {
                    $map[$memberDn] = [];
                }

                $map[$memberDn][] = $cn;
            }
        }

        foreach ($map as $dn => $roles) {
            $roles = array_values(array_unique($roles));
            sort($roles);
            $map[$dn] = $roles;
        }

        return $map;
    }
}
