<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatePanelAccess
{
    public function __construct(
        private readonly AuthenticateSSO $authenticateSSO
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldUseLocalAdmin()) {
            if ($request->session()->get('local_admin_logged_in') === true) {
                $sessionUser = $request->session()->get('local_admin_user', []);

                $user = new User();
                $user->id = $sessionUser['id'] ?? 'local-admin';
                $user->name = $sessionUser['name'] ?? env('LOCAL_ADMIN_NAME', 'Local Admin');
                $user->email = $sessionUser['email'] ?? env('LOCAL_ADMIN_EMAIL', 'local@example.com');
                $user->is_local_admin = (bool) ($sessionUser['is_local_admin'] ?? true);

                Auth::setUser($user);

                return $next($request);
            }

            return new RedirectResponse(route('local.login'));
        }

        return $this->authenticateSSO->handle($request, $next);
    }

    private function shouldUseLocalAdmin(): bool
    {
        return app()->environment('local')
            && filter_var(env('LOCAL_ADMIN_ENABLED', false), FILTER_VALIDATE_BOOL);
    }
}
