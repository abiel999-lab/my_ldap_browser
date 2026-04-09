<?php

namespace App\Services\Ldap;

use App\Models\LdapUnitView;

class LdapUnitSyncService
{
    protected string $unitsDn = 'ou=units,ou=groups,dc=petra,dc=ac,dc=id';

    public function __construct(
        protected LdapNativeService $ldap,
    ) {
    }

    public function sync(): int
    {
        $entries = $this->ldap->search(
            $this->unitsDn,
            '(objectClass=groupOfNames)',
            ['cn', 'description']
        );

        $activeDns = [];
        $count = 0;

        for ($i = 0; $i < ($entries['count'] ?? 0); $i++) {
            $entry = $entries[$i];
            $dn = $this->ldap->normalizeDn($entry['dn']);

            if (! $this->isDirectUnitDn($dn)) {
                continue;
            }

            LdapUnitView::updateOrCreate(
                ['dn' => $dn],
                [
                    'cn' => $this->ldap->extractFirst($entry, 'cn') ?? 'unknown',
                    'description' => $this->ldap->extractFirst($entry, 'description'),
                    'synced_at' => now(),
                ]
            );

            $activeDns[] = $dn;
            $count++;
        }

        if (! empty($activeDns)) {
            LdapUnitView::query()->whereNotIn('dn', $activeDns)->delete();
        } else {
            LdapUnitView::query()->delete();
        }

        return $count;
    }

    protected function isDirectUnitDn(string $dn): bool
    {
        $normalized = strtolower($dn);
        $suffix = strtolower(',' . $this->unitsDn);

        if (! str_ends_with($normalized, $suffix)) {
            return false;
        }

        $left = substr($normalized, 0, -strlen($suffix));

        return substr_count($left, ',') === 0 && str_starts_with($left, 'cn=');
    }
}
