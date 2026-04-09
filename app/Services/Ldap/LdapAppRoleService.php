<?php

namespace App\Services\Ldap;

use RuntimeException;

class LdapAppRoleService
{
    protected string $appsDn = 'ou=apps,ou=groups,dc=petra,dc=ac,dc=id';
    protected string $dummyDn = 'uid=dummy,ou=services,dc=petra,dc=ac,dc=id';

    public function __construct(
        protected LdapNativeService $ldap,
        protected LdapAppSyncService $syncService,
    ) {
    }

    public function createApp(string $appCn, ?string $description = null): void
    {
        $appCn = trim($appCn);

        if ($appCn === '') {
            throw new RuntimeException('Nama app wajib diisi.');
        }

        $dn = "cn={$appCn},{$this->appsDn}";

        if ($this->ldap->read($dn, ['cn'])) {
            throw new RuntimeException("App {$appCn} sudah ada.");
        }

        $entry = [
            'objectClass' => ['top', 'groupOfNames'],
            'cn' => $appCn,
            'member' => [$this->dummyDn],
        ];

        if ($description) {
            $entry['description'] = $description;
        }

        $this->ldap->add($dn, $entry);
        $this->syncService->sync();
    }

    public function createRole(string $appCn, string $roleCn): void
    {
        $appCn = trim($appCn);
        $roleCn = trim($roleCn);

        if ($appCn === '' || $roleCn === '') {
            throw new RuntimeException('App dan role wajib diisi.');
        }

        $appDn = "cn={$appCn},{$this->appsDn}";
        $roleDn = "cn={$roleCn},{$appDn}";

        if (! $this->ldap->read($appDn, ['cn'])) {
            throw new RuntimeException("App {$appCn} tidak ditemukan.");
        }

        if ($this->ldap->read($roleDn, ['cn'])) {
            throw new RuntimeException("Role {$roleCn} sudah ada di app {$appCn}.");
        }

        $entry = [
            'objectClass' => ['top', 'groupOfNames'],
            'cn' => $roleCn,
            'member' => [$this->dummyDn],
        ];

        $this->ldap->add($roleDn, $entry);
        $this->syncService->sync();
    }

    public function deleteApp(string $dn): void
    {
        $children = $this->ldap->search($dn, '(objectClass=groupOfNames)', ['cn']);

        for ($i = 0; $i < ($children['count'] ?? 0); $i++) {
            $childDn = $this->ldap->normalizeDn($children[$i]['dn']);

            if ($childDn !== $this->ldap->normalizeDn($dn)) {
                $this->ldap->delete($childDn);
            }
        }

        $this->ldap->delete($dn);
        $this->syncService->sync();
    }

    public function deleteRole(string $roleDn): void
    {
        $this->ldap->delete($roleDn);
        $this->syncService->sync();
    }
}
