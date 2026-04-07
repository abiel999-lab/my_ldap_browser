<?php

namespace App\Providers;

use App\Mail\Transport\PetraNotifikasiTransport;
use Illuminate\Mail\MailManager;
use Illuminate\Support\ServiceProvider;

class PetraMailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Nothing needed here for the mailer
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->make(MailManager::class)->extend(
            'petra_notifikasi',
            function (array $config) {
                return new PetraNotifikasiTransport(
                    apiUrl: $config['api_url'],
                    username: $config['username'],
                    password: $config['password'],
                );
            }
        );
    }
}
