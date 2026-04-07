<?php

namespace App\Console\Commands\Ldap;

use App\Services\Ldap\LdapUserSyncService;
use Illuminate\Console\Command;

class SyncLdapUsersView extends Command
{
    protected $signature = 'ldap:sync-users-view';

    protected $description = 'Sync LDAP users into local Eloquent view table for Filament';

    public function handle(LdapUserSyncService $service): int
    {
        $count = $service->sync();

        $this->info("LDAP users synced: {$count}");

        return self::SUCCESS;
    }
}
