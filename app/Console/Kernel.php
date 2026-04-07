<?php

namespace App\Console;

use App\Console\Commands\Ldap\SyncLdapRolesView;
use App\Console\Commands\Ldap\SyncLdapUsersView;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        SyncLdapUsersView::class,
        SyncLdapRolesView::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        //
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
