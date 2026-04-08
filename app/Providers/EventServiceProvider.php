<?php

namespace App\Providers;

use Aacotroneo\Saml2\Events\Saml2LoginEvent;
use App\Listeners\HandleSamlLogin;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Saml2LoginEvent::class => [
            HandleSamlLogin::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
