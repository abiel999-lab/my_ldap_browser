<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;

class AuthService
{
    public function auth($sso_cookie)
    {
        // -----------cek apakah ada login as
        if (session()->has('login_as')) {
            $response = [
                'status' => 1,
                'data' => [
                    'user' => session()->get('login_as'),
                ],
            ];

            return $response;
        }

        if (Cache::store('redis_auth')->has($sso_cookie)) {
            return Cache::store('redis_auth')->get($sso_cookie);
        } else {
            $payload = [
                config('sso.cookie') => $sso_cookie,
                'app_url_api' => config('app.url_api'),
                'app_id' => config('app.id'),
            ];
            $response = Http::withoutVerifying()->retry(3, 100)->post(config('url.service.api.auth') . '/api/auth', $payload);
            if ($response['status'] == 1) {
                Cache::store('redis_auth')->forever($sso_cookie, $response->json());
            } else {
                Cache::store('redis_auth')->forget($sso_cookie);
            }
        }

        return $response;
    }
}
