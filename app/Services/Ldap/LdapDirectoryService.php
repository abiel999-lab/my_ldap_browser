<?php

namespace App\Services\Ldap;

use RuntimeException;
use ZipArchive;

class LdapDirectoryService
{
    protected $connection = null;

    public function connect()
    {
        if ($this->connection) {
            return $this->connection;
        }

        $host = env('LDAP_HOST', '127.0.0.1');
        $port = (int) env('LDAP_PORT', 389);
        $bindDn = env('LDAP_USERNAME') ?: env('LDAP_BIND_DN');
        $bindPassword = env('LDAP_PASSWORD') ?: env('LDAP_BIND_PASSWORD');

        $connection = @ldap_connect($host, $port);

        if (! $connection) {
            throw new RuntimeException('Gagal connect ke LDAP.');
        }

        ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);

        if (! @ldap_bind($connection, $bindDn, $bindPassword)) {
            throw new RuntimeException('Gagal bind ke LDAP. Cek DN dan password.');
        }

        $this->connection = $connection;

        return $this->connection;
    }

    public function getBaseDn(): string
    {
        return env('LDAP_BASE_DN', 'dc=petra,dc=ac,dc=id');
    }

    public function search(string $baseDn, string $filter = '(objectClass=*)', array $attributes = ['*', '+']): array
    {
        $conn = $this->connect();

        $search = @ldap_search($conn, $baseDn, $filter, $attributes);

        if (! $search) {
            throw new RuntimeException('LDAP search gagal pada base DN: ' . $baseDn);
        }

        $entries = @ldap_get_entries($conn, $search);

        if (! is_array($entries)) {
            return [];
        }

        $result = [];

        for ($i = 0; $i < ($entries['count'] ?? 0); $i++) {
            $result[] = $entries[$i];
        }

        return $result;
    }

    public function findByUid(string $uid, ?string $baseDn = null): ?array
    {
        $baseDn ??= $this->getBaseDn();

        $entries = $this->search($baseDn, '(uid=' . ldap_escape($uid, '', LDAP_ESCAPE_FILTER) . ')');

        return $entries[0] ?? null;
    }

    public function add(string $dn, array $entry): void
    {
        $conn = $this->connect();

        if (! @ldap_add($conn, $dn, $entry)) {
            throw new RuntimeException('Gagal add LDAP entry: ' . $dn);
        }
    }

    public function modify(string $dn, array $replace): void
    {
        $conn = $this->connect();

        if (! @ldap_modify($conn, $dn, $replace)) {
            throw new RuntimeException('Gagal modify LDAP entry: ' . $dn);
        }
    }

    public function buildDnFromRow(array $row): string
    {
        $uid = trim((string) ($row['uid'] ?? ''));
        $ou = trim((string) ($row['ou'] ?? 'people'));
        $baseDn = $this->getBaseDn();

        if ($uid === '') {
            throw new RuntimeException('UID kosong.');
        }

        if (str_contains(strtolower($ou), 'ou=')) {
            return 'uid=' . $uid . ',' . $ou . ',' . $baseDn;
        }

        return 'uid=' . $uid . ',ou=' . $ou . ',' . $baseDn;
    }

    public function entryToLdif(array $entry): string
    {
        $lines = [];

        $dn = $entry['dn'] ?? null;

        if (! $dn) {
            return '';
        }

        $lines[] = 'dn: ' . $dn;

        foreach ($entry as $attr => $value) {
            if (is_int($attr) || $attr === 'count' || $attr === 'dn') {
                continue;
            }

            if (! is_array($value)) {
                continue;
            }

            $count = $value['count'] ?? 0;

            for ($i = 0; $i < $count; $i++) {
                if (! array_key_exists($i, $value)) {
                    continue;
                }

                $lines[] = $attr . ': ' . $value[$i];
            }
        }

        return implode("\n", $lines) . "\n\n";
    }

    public function entriesToLdif(array $entries): string
    {
        $content = '';

        foreach ($entries as $entry) {
            $content .= $this->entryToLdif($entry);
        }

        return $content;
    }

    public function writeArtifactFiles(string $prefix, string $ldifContent, array $manifest = []): array
    {
        $dir = storage_path('app/private/ldap-artifacts/' . date('Ymd'));

        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $stamp = date('Ymd_His');
        $ldifName = $prefix . '_' . $stamp . '.ldif';
        $zipName = $prefix . '_' . $stamp . '.zip';

        $ldifPath = $dir . DIRECTORY_SEPARATOR . $ldifName;
        $zipPath = $dir . DIRECTORY_SEPARATOR . $zipName;
        $manifestPath = $dir . DIRECTORY_SEPARATOR . $prefix . '_' . $stamp . '_manifest.json';

        file_put_contents($ldifPath, $ldifContent);
        file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Gagal membuat ZIP artifact.');
        }

        $zip->addFile($ldifPath, $ldifName);
        $zip->addFile($manifestPath, basename($manifestPath));
        $zip->close();

        return [
            'ldif_path' => str_replace(base_path() . DIRECTORY_SEPARATOR, '', $ldifPath),
            'zip_path' => str_replace(base_path() . DIRECTORY_SEPARATOR, '', $zipPath),
        ];
    }

    public function normalizeImportRow(array $row): array
    {
        $normalized = [];

        foreach ($row as $key => $value) {
            $normalized[trim((string) $key)] = is_string($value) ? trim($value) : $value;
        }

        return $normalized;
    }

    public function rowToLdapAttributes(array $row): array
    {
        $uid = trim((string) ($row['uid'] ?? ''));
        $cn = trim((string) ($row['cn'] ?? $uid));
        $sn = trim((string) ($row['sn'] ?? $cn));
        $givenName = trim((string) ($row['givenName'] ?? ''));
        $mail = trim((string) ($row['mail'] ?? ''));
        $userPassword = trim((string) ($row['userPassword'] ?? ''));
        $petraAffiliation = trim((string) ($row['petraAffiliation'] ?? ''));
        $userNIK = trim((string) ($row['userNIK'] ?? ''));
        $employeeNumber = trim((string) ($row['employeeNumber'] ?? ''));
        $studentNumber = trim((string) ($row['studentNumber'] ?? ''));

        if ($uid === '') {
            throw new RuntimeException('uid wajib diisi.');
        }

        $attributes = [
            'objectClass' => ['top', 'person', 'organizationalPerson', 'inetOrgPerson', 'petraPerson'],
            'uid' => $uid,
            'cn' => $cn,
            'sn' => $sn,
        ];

        if ($givenName !== '') {
            $attributes['givenName'] = $givenName;
        }

        if ($mail !== '') {
            $attributes['mail'] = $mail;
        }

        if ($userPassword !== '') {
            $attributes['userPassword'] = $userPassword;
        }

        if ($petraAffiliation !== '') {
            $attributes['petraAffiliation'] = $petraAffiliation;
        }

        if ($userNIK !== '') {
            $attributes['userNIK'] = $userNIK;
        }

        if ($employeeNumber !== '') {
            $attributes['employeeNumber'] = $employeeNumber;
        }

        if ($studentNumber !== '') {
            $attributes['studentNumber'] = $studentNumber;
        }

        return $attributes;
    }
}
