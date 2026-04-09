<?php

namespace App\Services\Ldap;

use Exception;

class LdapNativeService
{
    protected $connection = null;

    public function connect()
    {
        if ($this->connection) {
            return $this->connection;
        }

        $host = env('LDAP_HOST');
        $port = (int) env('LDAP_PORT', 389);

        $conn = ldap_connect($host, $port);

        if (! $conn) {
            throw new Exception('Failed to connect to LDAP server.');
        }

        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
        $timeout = (int) env('LDAP_TIMEOUT', 5);
        ldap_set_option($conn, LDAP_OPT_NETWORK_TIMEOUT, $timeout);
        ldap_set_option($conn, LDAP_OPT_TIMELIMIT, $timeout);

        $bind = @ldap_bind(
            $conn,
            env('LDAP_USERNAME'),
            env('LDAP_PASSWORD')
        );

        if (! $bind) {
            throw new Exception('Failed to bind LDAP: ' . ldap_error($conn));
        }

        $this->connection = $conn;

        return $conn;
    }

    public function disconnect(): void
    {
        if ($this->connection) {
            @ldap_unbind($this->connection);
            $this->connection = null;
        }
    }

    public function add(string $dn, array $entry): void
    {
        $conn = $this->connect();

        $ok = @ldap_add($conn, $dn, $entry);

        if (! $ok) {
            throw new Exception('LDAP add failed: ' . ldap_error($conn));
        }
    }

    public function modify(string $dn, array $entry): void
    {
        $conn = $this->connect();

        $ok = @ldap_modify($conn, $dn, $entry);

        if (! $ok) {
            throw new Exception('LDAP modify failed: ' . ldap_error($conn));
        }
    }

    public function modAdd(string $dn, array $entry): void
    {
        $conn = $this->connect();

        $ok = @ldap_mod_add($conn, $dn, $entry);

        if (! $ok) {
            throw new Exception('LDAP mod_add failed: ' . ldap_error($conn));
        }
    }

    public function modDel(string $dn, array $entry): void
    {
        $conn = $this->connect();

        $ok = @ldap_mod_del($conn, $dn, $entry);

        if (! $ok) {
            throw new Exception('LDAP mod_del failed: ' . ldap_error($conn));
        }
    }

    public function modReplace(string $dn, array $entry): void
    {
        $conn = $this->connect();

        $ok = @ldap_mod_replace($conn, $dn, $entry);

        if (! $ok) {
            throw new Exception('LDAP mod_replace failed: ' . ldap_error($conn));
        }
    }

    public function delete(string $dn): void
    {
        $conn = $this->connect();

        $ok = @ldap_delete($conn, $dn);

        if (! $ok) {
            throw new Exception('LDAP delete failed: ' . ldap_error($conn));
        }
    }

    public function read(string $dn, array $attributes = []): ?array
    {
        $conn = $this->connect();

        $search = @ldap_read($conn, $dn, '(objectClass=*)', $attributes ?: ['*']);

        if (! $search) {
            $error = ldap_error($conn);

            if (stripos($error, 'No such object') !== false) {
                return null;
            }

            throw new Exception('LDAP read failed: ' . $error);
        }

        $entries = ldap_get_entries($conn, $search);

        if (! isset($entries['count']) || $entries['count'] < 1) {
            return null;
        }

        return $entries[0];
    }

    public function search(string $baseDn, string $filter, array $attributes = []): array
    {
        $conn = $this->connect();

        $search = @ldap_search($conn, $baseDn, $filter, $attributes ?: ['*']);

        if (! $search) {
            throw new Exception('LDAP search failed: ' . ldap_error($conn));
        }

        return ldap_get_entries($conn, $search);
    }

    public function extractFirst(array $entry, string $attribute): ?string
    {
        $key = strtolower($attribute);

        if (! isset($entry[$key])) {
            return null;
        }

        if (is_array($entry[$key])) {
            return $entry[$key][0] ?? null;
        }

        return $entry[$key] ?: null;
    }

    public function extractMany(array $entry, string $attribute): array
    {
        $key = strtolower($attribute);

        if (! isset($entry[$key]) || ! is_array($entry[$key])) {
            return [];
        }

        $values = [];
        foreach ($entry[$key] as $k => $v) {
            if ($k === 'count') {
                continue;
            }

            if ($v !== null && $v !== '') {
                $values[] = $v;
            }
        }

        return array_values($values);
    }

    public function normalizeMultiValues(?string $text): array
    {
        if ($text === null || trim($text) === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            'trim',
            preg_split('/\r\n|\r|\n/', $text)
        )));
    }

    public function normalizeDn(string $dn): string
    {
        $dn = strtolower(trim($dn));
        $dn = preg_replace('/\s*,\s*/', ',', $dn);
        $dn = preg_replace('/\s*=\s*/', '=', $dn);

        return $dn;
    }
}
