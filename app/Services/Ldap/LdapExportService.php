<?php

namespace App\Services\Ldap;

use App\Models\LdapExport;
use Throwable;

class LdapExportService
{
    public function __construct(
        protected LdapDirectoryService $ldapDirectoryService,
    ) {
    }

    public function run(LdapExport $export): void
    {
        try {
            $export->update([
                'status' => 'running',
                'error_message' => null,
            ]);

            $baseDn = $this->resolveBaseDn($export);
            $filter = $export->filter ?: '(objectClass=*)';

            $entries = $this->ldapDirectoryService->search($baseDn, $filter);
            $ldif = $this->ldapDirectoryService->entriesToLdif($entries);

            $files = $this->ldapDirectoryService->writeArtifactFiles(
                prefix: 'ldap_export',
                ldifContent: $ldif,
                manifest: [
                    'type' => 'export',
                    'scope' => $export->scope,
                    'base_dn' => $baseDn,
                    'filter' => $filter,
                    'total_entries' => count($entries),
                    'created_at' => now()->toDateTimeString(),
                ],
            );

            $export->update([
                'status' => 'success',
                'total_entries' => count($entries),
                'ldif_path' => $files['ldif_path'],
                'zip_path' => $files['zip_path'],
            ]);
        } catch (Throwable $e) {
            $export->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    protected function resolveBaseDn(LdapExport $export): string
    {
        $baseDn = $this->ldapDirectoryService->getBaseDn();

        return match ($export->scope) {
            'people' => 'ou=people,' . $baseDn,
            'groups' => 'ou=groups,' . $baseDn,
            'roles' => 'ou=roles,ou=groups,' . $baseDn,
            'custom' => $export->base_dn ?: $baseDn,
            default => $baseDn,
        };
    }
}
