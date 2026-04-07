<?php

namespace App\Http\Middleware;

use App\Helpers\Auth\AuthHelper;
use App\Services\AuthService;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthenticateSSO
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response|RedirectResponse)  $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $authenticated = $this->checkCookie($request);

        if ($authenticated) {
            AuthHelper::setUser($authenticated);

            return $next($request);
        }

        $param = [
            'app' => config('app.kode'),
            'redirect_url' => Str::start($request->path(), '/'),
            'auth_callback_url' => '/auth_callback',
        ];

        if (config('app.env') === 'local') {
            $param['app_url'] = config('app.url');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $authServiceUrl = rtrim((string) env('AUTH_SERVICE_URL', config('url.service.auth')), '/');
        $authLoginPath = (string) env('AUTH_LOGIN_PATH', '/login');

        if ($authLoginPath === '') {
            $authLoginPath = '/login';
        }

        $authLoginPath = '/' . ltrim($authLoginPath, '/');

        return redirect()->away($authServiceUrl . $authLoginPath . '?' . Arr::query($param));
    }

    private function checkCookie(Request $request): mixed
    {
        if ($request->session()->get(config('sso.cookie'))) {
            $response = (new AuthService())->auth($request->session()->get(config('sso.cookie')));

            if (($response['status'] ?? 0) == 1) {
                return $response['data']['user'] ?? false;
            }
        }

        return false;
    }
}
