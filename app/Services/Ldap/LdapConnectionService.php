<?php

declare(strict_types=1);

namespace App\Services\Ldap;

use RuntimeException;

class LdapConnectionService
{
    public function connect()
    {
        return $this->connectWithCredentials(
            bindDn: (string) config('ldap_admin.bind_dn'),
            bindPassword: (string) config('ldap_admin.bind_password'),
        );
    }

    public function connectSchema()
    {
        $schemaBindDn = (string) config('ldap_admin.schema_bind_dn');
        $schemaBindPassword = (string) config('ldap_admin.schema_bind_password');

        if ($schemaBindDn === '') {
            throw new RuntimeException('LDAP schema bind DN belum di-set.');
        }

        return $this->connectWithCredentials(
            bindDn: $schemaBindDn,
            bindPassword: $schemaBindPassword,
        );
    }

    protected function connectWithCredentials(string $bindDn, string $bindPassword)
    {
        $host = (string) config('ldap_admin.host');
        $port = (int) config('ldap_admin.port');
        $timeout = (int) config('ldap_admin.timeout');
        $useSsl = (bool) config('ldap_admin.use_ssl');
        $useTls = (bool) config('ldap_admin.use_tls');

        $uri = $useSsl ? "ldaps://{$host}" : $host;

        $connection = ldap_connect($uri, $port);

        if (! $connection) {
            throw new RuntimeException('Failed to initialize LDAP connection.');
        }

        ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($connection, LDAP_OPT_NETWORK_TIMEOUT, $timeout);

        if ($useTls) {
            if (! @ldap_start_tls($connection)) {
                throw new RuntimeException('Failed to start LDAP TLS: ' . $this->getLastError($connection));
            }
        }

        if (! @ldap_bind($connection, $bindDn, $bindPassword)) {
            throw new RuntimeException('LDAP bind failed: ' . $this->getLastError($connection));
        }

        return $connection;
    }

    public function getRootDse(): array
    {
        $connection = $this->connect();

        $search = @ldap_read($connection, '', '(objectClass=*)', ['*', '+']);

        if (! $search) {
            throw new RuntimeException('Failed to read RootDSE: ' . $this->getLastError($connection));
        }

        $entries = ldap_get_entries($connection, $search);

        if (! isset($entries[0])) {
            return [];
        }

        return $this->normalizeEntry($entries[0]);
    }

    public function getLastError($connection): string
    {
        return ldap_error($connection) ?: 'Unknown LDAP error';
    }

    public function normalizeEntry(array $entry): array
    {
        $normalized = [
            'dn' => $entry['dn'] ?? null,
            'attributes' => [],
        ];

        foreach ($entry as $key => $value) {
            if (is_int($key) || $key === 'count' || $key === 'dn') {
                continue;
            }

            $attributeName = (string) $key;
            $values = [];

            if (is_array($value)) {
                foreach ($value as $valueKey => $item) {
                    if ($valueKey === 'count' || is_int($valueKey)) {
                        if (is_int($valueKey) && $item !== null) {
                            $values[] = (string) $item;
                        }
                    }
                }
            } elseif ($value !== null) {
                $values[] = (string) $value;
            }

            $normalized['attributes'][$attributeName] = $values;
        }

        return $normalized;
    }

    public function normalizeEntries(array $entries): array
    {
        $result = [];

        $count = (int) ($entries['count'] ?? 0);

        for ($index = 0; $index < $count; $index++) {
            if (isset($entries[$index]) && is_array($entries[$index])) {
                $result[] = $this->normalizeEntry($entries[$index]);
            }
        }

        return $result;
    }
}
