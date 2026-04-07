<?php

namespace App\Services\Ldap;

use App\Models\LdapBackup;
use Throwable;

class LdapBackupService
{
    public function __construct(
        protected LdapDirectoryService $ldapDirectoryService,
    ) {
    }

    public function run(LdapBackup $backup): void
    {
        try {
            $backup->update([
                'status' => 'running',
                'error_message' => null,
            ]);

            $baseDn = $backup->base_dn ?: $this->ldapDirectoryService->getBaseDn();
            $entries = $this->ldapDirectoryService->search($baseDn);
            $ldif = $this->ldapDirectoryService->entriesToLdif($entries);

            $files = $this->ldapDirectoryService->writeArtifactFiles(
                prefix: 'ldap_backup',
                ldifContent: $ldif,
                manifest: [
                    'type' => 'backup',
                    'scope' => $backup->scope,
                    'base_dn' => $baseDn,
                    'total_entries' => count($entries),
                    'created_at' => now()->toDateTimeString(),
                ],
            );

            $backup->update([
                'status' => 'success',
                'total_entries' => count($entries),
                'ldif_path' => $files['ldif_path'],
                'zip_path' => $files['zip_path'],
            ]);
        } catch (Throwable $e) {
            $backup->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
