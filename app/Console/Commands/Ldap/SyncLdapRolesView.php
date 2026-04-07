<?php

namespace App\Console\Commands\Ldap;

use App\Services\Ldap\LdapRoleSyncService;
use Illuminate\Console\Command;

class SyncLdapRolesView extends Command
{
    protected $signature = 'ldap:sync-roles-view';

    protected $description = 'Sync LDAP roles into local Eloquent view table for Filament';

    public function handle(LdapRoleSyncService $service): int
    {
        $count = $service->sync();

        $this->info("LDAP roles synced: {$count}");

        return self::SUCCESS;
    }
}
