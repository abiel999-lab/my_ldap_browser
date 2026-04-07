<?php

namespace App\Console\Commands;

use Artisan;
use Illuminate\Console\Command;

class ReloadCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:reload';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('filament:optimize-clear');
        // \Artisan::call('octane:reload');
        Artisan::call('view:cache');
        Artisan::call('route:cache');
        Artisan::call('config:cache');
        Artisan::call('filament:optimize');
        Artisan::call('icons:cache');
        // \Artisan::call('octane:cache');
    }
}
