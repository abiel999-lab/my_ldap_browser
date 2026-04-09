<?php

namespace App\Services\Ldap;

use RuntimeException;

class LdapUnitService
{
    protected string $unitsDn = 'ou=units,ou=groups,dc=petra,dc=ac,dc=id';
    protected string $dummyDn = 'uid=dummy,ou=services,dc=petra,dc=ac,dc=id';

    public function __construct(
        protected LdapNativeService $ldap,
        protected LdapUnitSyncService $syncService,
    ) {
    }

    public function create(string $cn, ?string $description = null): void
    {
        $cn = trim($cn);

        if ($cn === '') {
            throw new RuntimeException('Nama unit wajib diisi.');
        }

        $dn = "cn={$cn},{$this->unitsDn}";

        if ($this->ldap->read($dn, ['cn'])) {
            throw new RuntimeException("Unit {$cn} sudah ada.");
        }

        $entry = [
            'objectClass' => ['top', 'groupOfNames'],
            'cn' => $cn,
            'member' => [$this->dummyDn],
        ];

        if ($description) {
            $entry['description'] = $description;
        }

        $this->ldap->add($dn, $entry);
        $this->syncService->sync();
    }

    public function update(string $dn, string $cn, ?string $description = null): void
    {
        $cn = trim($cn);

        if ($cn === '') {
            throw new RuntimeException('Nama unit wajib diisi.');
        }

        $entry = [
            'cn' => $cn,
        ];

        if ($description !== null) {
            $entry['description'] = $description;
        }

        $this->ldap->modify($dn, $entry);
        $this->syncService->sync();
    }

    public function delete(string $dn): void
    {
        $this->ldap->delete($dn);
        $this->syncService->sync();
    }
}
