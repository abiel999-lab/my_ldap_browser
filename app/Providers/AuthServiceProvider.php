<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Gate\User;
use App\Models\Ref\Bahasa;
use App\Policies\Admin\BahasaPolicy;
use App\Policies\Admin\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        Bahasa::class => BahasaPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function ($user, string $ability) {
            if (($user->is_local_admin ?? false) === true) {
                return true;
            }

            return null;
        });
    }
}
