<?php

namespace App\Services\Ldap;

use Exception;

class LdapUserCrudService
{
    protected string $peopleDn = 'ou=people,dc=petra,dc=ac,dc=id';

    public function __construct(
        protected LdapNativeService $ldap,
    ) {
    }

    public function create(array $data): array
    {
        $uid = trim((string) ($data['uid'] ?? ''));

        if ($uid === '') {
            throw new Exception('UID wajib diisi.');
        }

        $dn = "uid={$uid},{$this->peopleDn}";

        $existing = $this->ldap->read($dn, ['dn']);
        if ($existing) {
            throw new Exception("User {$uid} sudah ada di LDAP.");
        }

        $cn = trim((string) ($data['cn'] ?? ''));
        $sn = trim((string) ($data['sn'] ?? ''));
        $password = trim((string) ($data['password'] ?? ''));

        if ($cn === '' || $sn === '') {
            throw new Exception('Common Name dan Surname wajib diisi.');
        }

        if ($password === '') {
            throw new Exception('Password wajib diisi.');
        }

        $entry = [
            'objectClass' => [
                'top',
                'person',
                'organizationalPerson',
                'inetOrgPerson',
                'petraPerson',
            ],
            'uid' => $uid,
            'cn' => $cn,
            'sn' => $sn,
            'userPassword' => $password,
        ];

        $this->putIfFilled($entry, 'displayName', $data['display_name'] ?? null);
        $this->putIfFilled($entry, 'givenName', $data['given_name'] ?? null);
        $this->putIfFilled($entry, 'mail', $data['mail'] ?? null);

        $this->putIfFilled($entry, 'employeeNumber', $data['employee_number'] ?? null);
        $this->putIfFilled($entry, 'userNIK', $data['user_nik'] ?? null);
        $this->putIfFilled($entry, 'petraAccountStatus', $data['petra_account_status'] ?? null);
        $this->putIfFilled($entry, 'studentNumber', $data['student_number'] ?? null);

        $this->putManyIfFilled($entry, 'mailAlternateAddress', $data['mail_alternate_address_text'] ?? null);
        $this->putManyIfFilled($entry, 'studentNumberHistory', $data['student_number_history_text'] ?? null);

        $this->ldap->connect();
        $this->ldap->add($dn, $entry);

        $verified = $this->ldap->read($dn, ['*']);

        if (! $verified) {
            throw new Exception('User tidak ditemukan di LDAP setelah proses create.');
        }

        return [
            'dn' => $dn,
            'entry' => $verified,
        ];
    }

    public function updateByDn(string $dn, array $data): array
    {
        $existing = $this->ldap->read($dn, ['*']);

        if (! $existing) {
            throw new Exception("LDAP user dengan DN {$dn} tidak ditemukan.");
        }

        $cn = trim((string) ($data['cn'] ?? ''));
        $sn = trim((string) ($data['sn'] ?? ''));

        if ($cn === '' || $sn === '') {
            throw new Exception('Common Name dan Surname wajib diisi.');
        }

        $entry = [
            'cn' => $cn,
            'sn' => $sn,
        ];

        $this->putIfFilled($entry, 'displayName', $data['display_name'] ?? null);
        $this->putIfFilled($entry, 'givenName', $data['given_name'] ?? null);
        $this->putIfFilled($entry, 'mail', $data['mail'] ?? null);

        $this->putIfFilled($entry, 'employeeNumber', $data['employee_number'] ?? null);
        $this->putIfFilled($entry, 'userNIK', $data['user_nik'] ?? null);
        $this->putIfFilled($entry, 'petraAccountStatus', $data['petra_account_status'] ?? null);
        $this->putIfFilled($entry, 'studentNumber', $data['student_number'] ?? null);

        $this->putManyIfFilled($entry, 'mailAlternateAddress', $data['mail_alternate_address_text'] ?? null);
        $this->putManyIfFilled($entry, 'studentNumberHistory', $data['student_number_history_text'] ?? null);

        $password = trim((string) ($data['password'] ?? ''));
        if ($password !== '') {
            $entry['userPassword'] = $password;
        }

        $this->ldap->connect();
        $this->ldap->modify($dn, $entry);

        $verified = $this->ldap->read($dn, ['*']);

        if (! $verified) {
            throw new Exception("User {$dn} tidak ditemukan di LDAP setelah proses update.");
        }

        return [
            'dn' => $dn,
            'entry' => $verified,
        ];
    }

    public function deleteByDn(string $dn): array
    {
        $existing = $this->ldap->read($dn, ['dn']);

        if (! $existing) {
            throw new Exception("LDAP user dengan DN {$dn} tidak ditemukan.");
        }

        $this->ldap->connect();
        $this->ldap->delete($dn);

        $verified = $this->ldap->read($dn, ['dn']);

        if ($verified) {
            throw new Exception("User {$dn} masih ditemukan di LDAP setelah proses delete.");
        }

        return [
            'dn' => $dn,
        ];
    }

    protected function putIfFilled(array &$entry, string $attribute, mixed $value): void
    {
        if ($value === null) {
            return;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return;
        }

        $entry[$attribute] = $value;
    }

    protected function putManyIfFilled(array &$entry, string $attribute, mixed $text): void
    {
        $values = $this->ldap->normalizeMultiValues($text);

        if (empty($values)) {
            return;
        }

        $entry[$attribute] = $values;
    }
}
