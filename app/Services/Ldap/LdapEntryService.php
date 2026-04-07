<?php

declare(strict_types=1);

namespace App\Services\Ldap;

use RuntimeException;

class LdapEntryService
{
    public function __construct(
        private readonly LdapConnectionService $ldapConnectionService
    ) {
    }

    public function listChildren(string $baseDn): array
    {
        $connection = $this->ldapConnectionService->connect();

        $search = @ldap_list(
            $connection,
            $baseDn,
            '(objectClass=*)',
            ['*', '+']
        );

        if (! $search) {
            throw new RuntimeException('Failed to list LDAP children: '.$this->ldapConnectionService->getLastError($connection));
        }

        $entries = ldap_get_entries($connection, $search);
        $normalizedEntries = $this->ldapConnectionService->normalizeEntries($entries);

        return array_map(function (array $entry): array {
            $objectClasses = $entry['attributes']['objectclass'] ?? [];

            return [
                'dn' => $entry['dn'],
                'rdn' => $this->extractRdn($entry['dn']),
                'objectClasses' => $objectClasses,
                'label' => $this->buildLabel($entry),
                'isContainer' => $this->isContainer($objectClasses),
                'attributes' => $entry['attributes'],
            ];
        }, $normalizedEntries);
    }

    public function getEntry(string $dn): array
    {
        $connection = $this->ldapConnectionService->connect();

        $search = @ldap_read(
            $connection,
            $dn,
            '(objectClass=*)',
            ['*', '+']
        );

        if (! $search) {
            throw new RuntimeException('Failed to read LDAP entry: '.$this->ldapConnectionService->getLastError($connection));
        }

        $entries = ldap_get_entries($connection, $search);
        $normalizedEntries = $this->ldapConnectionService->normalizeEntries($entries);

        if (! isset($normalizedEntries[0])) {
            throw new RuntimeException("LDAP entry not found for DN: {$dn}");
        }

        $entry = $normalizedEntries[0];
        $entry['rdn'] = $this->extractRdn($entry['dn']);
        $entry['objectClasses'] = $entry['attributes']['objectclass'] ?? [];

        return $entry;
    }

    public function createEntry(string $dn, array $attributes): bool
    {
        $connection = $this->ldapConnectionService->connect();
        $payload = $this->prepareAttributesForWrite($attributes);

        if (! isset($payload['objectClass']) && ! isset($payload['objectclass'])) {
            throw new RuntimeException('objectClass wajib diisi saat create entry.');
        }
        $this->assertDnIsWritable($dn);
        $this->assertAttributesAreWritable($attributes);

        $result = @ldap_add($connection, $dn, $payload);

        if (! $result) {
            throw new RuntimeException('Failed to create LDAP entry: '.$this->ldapConnectionService->getLastError($connection));
        }

        return true;
    }

    public function replaceAttributes(string $dn, array $attributes): bool
    {
        $connection = $this->ldapConnectionService->connect();
        $payload = $this->prepareAttributesForWrite($attributes);

        $this->assertDnIsWritable($dn);
        $this->assertAttributesAreWritable($attributes);

        $result = @ldap_modify($connection, $dn, $payload);

        if (! $result) {
            throw new RuntimeException('Failed to replace LDAP attributes: '.$this->ldapConnectionService->getLastError($connection));
        }

        return true;
    }

    public function setAttribute(string $dn, string $attribute, array $values): bool
    {
        $this->assertDnIsWritable($dn);
        $this->assertAttributesAreWritable([$attribute => $values]);
        return $this->replaceAttributes($dn, [
            $attribute => $values,
        ]);
    }

    public function addAttributeValues(string $dn, string $attribute, array $values): bool
    {
        $this->assertDnIsWritable($dn);
        $this->assertAttributesAreWritable([$attribute => $values]);
        $connection = $this->ldapConnectionService->connect();

        $payload = [
            $attribute => array_values(array_filter($values, fn ($value) => $value !== null && $value !== '')),
        ];

        $result = @ldap_mod_add($connection, $dn, $payload);

        if (! $result) {
            throw new RuntimeException('Failed to add LDAP attribute values: '.$this->ldapConnectionService->getLastError($connection));
        }

        return true;
    }

    public function deleteAttribute(string $dn, string $attribute, ?array $values = null): bool
    {

        $this->assertDnIsWritable($dn);
        $this->assertAttributesAreWritable([$attribute => $values]);
        $connection = $this->ldapConnectionService->connect();

        if ($values === null) {
            $batch = [
                [
                    'attrib' => $attribute,
                    'modtype' => LDAP_MODIFY_BATCH_REMOVE_ALL,
                ],
            ];

            $result = @ldap_modify_batch($connection, $dn, $batch);

            if (! $result) {
                throw new RuntimeException('Failed to delete LDAP attribute: '.$this->ldapConnectionService->getLastError($connection));
            }

            return true;
        }

        $payload = [
            $attribute => array_values(array_filter($values, fn ($value) => $value !== null && $value !== '')),
        ];

        $result = @ldap_mod_del($connection, $dn, $payload);

        if (! $result) {
            throw new RuntimeException('Failed to delete LDAP attribute values: '.$this->ldapConnectionService->getLastError($connection));
        }

        return true;
    }

    public function addObjectClasses(string $dn, array $objectClasses): bool
    {
        $this->assertDnIsWritable($dn);
        return $this->addAttributeValues($dn, 'objectClass', $objectClasses);
    }

    public function removeObjectClasses(string $dn, array $objectClasses): bool
    {
        $this->assertDnIsWritable($dn);
        return $this->deleteAttribute($dn, 'objectClass', $objectClasses);
    }

    public function renameEntry(string $dn, string $newRdn, ?string $newParentDn = null, bool $deleteOldRdn = true): bool
    {
        $this->assertDnIsWritable($dn);

        if ($newParentDn !== null) {
            $this->assertDnIsWritable($newParentDn);
        }
        $connection = $this->ldapConnectionService->connect();

        $result = @ldap_rename($connection, $dn, $newRdn, $newParentDn, $deleteOldRdn);

        if (! $result) {
            throw new RuntimeException('Failed to rename LDAP entry: '.$this->ldapConnectionService->getLastError($connection));
        }

        return true;
    }

    public function deleteEntry(string $dn, bool $recursive = false): bool
    {
        $this->assertDnIsWritable($dn);
        $connection = $this->ldapConnectionService->connect();

        if ($recursive) {
            $children = $this->listChildren($dn);

            foreach ($children as $child) {
                $this->deleteEntry($child['dn'], true);
            }
        }

        $result = @ldap_delete($connection, $dn);

        if (! $result) {
            throw new RuntimeException('Failed to delete LDAP entry: '.$this->ldapConnectionService->getLastError($connection));
        }

        return true;
    }

    public function search(string $baseDn, string $filter = '(objectClass=*)', int $sizeLimit = 200): array
    {
        $connection = $this->ldapConnectionService->connect();

        $search = @ldap_search(
            $connection,
            $baseDn,
            $filter,
            ['*', '+'],
            0,
            $sizeLimit
        );

        if (! $search) {
            throw new RuntimeException('Failed to search LDAP: '.$this->ldapConnectionService->getLastError($connection));
        }

        $entries = ldap_get_entries($connection, $search);

        return $this->ldapConnectionService->normalizeEntries($entries);
    }

    private function prepareAttributesForWrite(array $attributes): array
    {
        $payload = [];

        foreach ($attributes as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (is_array($value)) {
                $payload[$key] = array_values(
                    array_map(
                        fn ($item) => (string) $item,
                        array_filter($value, fn ($item) => $item !== null && $item !== '')
                    )
                );
            } else {
                $payload[$key] = [(string) $value];
            }
        }

        return $payload;
    }

    private function extractRdn(string $dn): string
    {
        $parts = ldap_explode_dn($dn, 0);

        if (! is_array($parts) || ! isset($parts[0])) {
            return $dn;
        }

        return (string) $parts[0];
    }

    private function buildLabel(array $entry): string
    {
        $attributes = $entry['attributes'];

        foreach (['ou', 'cn', 'uid', 'dc', 'o'] as $attributeName) {
            if (! empty($attributes[$attributeName][0])) {
                return (string) $attributes[$attributeName][0];
            }
        }

        return $entry['dn'];
    }

    private function isContainer(array $objectClasses): bool
    {
        $objectClasses = array_map('strtolower', $objectClasses);

        return in_array('organizationalunit', $objectClasses, true)
            || in_array('organization', $objectClasses, true)
            || in_array('domain', $objectClasses, true)
            || in_array('dcobject', $objectClasses, true)
            || in_array('groupofnames', $objectClasses, true);
    }

    private function assertDnIsWritable(string $dn): void
    {
        $normalizedDn = mb_strtolower(trim($dn));

        foreach (config('ldap_admin.protected_dns', []) as $protectedDn) {
            if ($normalizedDn === mb_strtolower(trim((string) $protectedDn))) {
                throw new \RuntimeException("DN '{$dn}' is protected and cannot be modified.");
            }
        }

        foreach (config('ldap_admin.protected_dn_suffixes', []) as $suffix) {
            $normalizedSuffix = mb_strtolower(trim((string) $suffix));

            if ($normalizedSuffix !== '' && str_ends_with($normalizedDn, $normalizedSuffix)) {
                throw new \RuntimeException("DN '{$dn}' is protected by suffix rule and cannot be modified.");
            }
        }
    }

    private function assertAttributesAreWritable(array $attributes): void
    {
        $forbiddenWriteAttributes = array_map(
            fn ($attribute): string => mb_strtolower((string) $attribute),
            config('ldap_admin.forbidden_write_attributes', [])
        );

        foreach ($attributes as $attributeName => $value) {
            $normalizedAttributeName = mb_strtolower((string) $attributeName);

            if (in_array($normalizedAttributeName, $forbiddenWriteAttributes, true)) {
                throw new \RuntimeException("Attribute '{$attributeName}' is forbidden to modify.");
            }
        }
    }
}