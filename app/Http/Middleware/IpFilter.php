<?php

namespace App\Http\Middleware;

use App\Models\Gate\App;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Response;

class IpFilter
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (
            $this->isHostKubernetes($request) // Cek apakah request berasal dari Kubernetes
            || $this->isHostApiGateway($request) // Cek apakah request berasal dari API Gateway
            || $this->isIpInWhitelist($request) // Cek apakah IP ada di whitelist
            || $this->isAppSecretValid($request) // Cek apakah app_secret valid
        ) {
            return $next($request); // Jika salah satu dari pengecekan di atas benar, lanjutkan permintaan
        }

        // Cek jika request dari API
        if ($request->is('api/*')) {
            return response()->json([
                'status' => 0,
                'message' => 'Unauthorized! You are not allowed to access this resource.',
            ], 401);
        }
        // Laravel return 401 Unauthorized response
        abort(401);
    }

    private function isHostApiGateway(Request $request): bool
    {
        if (! isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            return false; // If the header is not set, we cannot validate it
        }

        return $_SERVER['HTTP_X_FORWARDED_HOST'] === env('HOST_API_GATEWAY');
    }

    private function isHostKubernetes(Request $request): bool
    {
        $requestIp = $request->ip();
        $ipWhitelist = array_merge([
            '127.0.0.1', // Localhost
        ], array_filter(explode(',', str_replace(' ', '', env('KUBERNETES_CIDR', '')))));

        return IpUtils::checkIp($requestIp, $ipWhitelist);
    }

    private function isIpInWhitelist(Request $request): bool
    {
        $requestIps = $request->ips();
        // env('IP_WHITELIST') should be a comma-separated list of IPs or CIDR ranges, e.g. "
        $ipWhitelist = array_filter(explode(',', str_replace(' ', '', env('IP_WHITELIST', ''))));

        return IpUtils::checkIp(end($requestIps), $ipWhitelist);
    }

    private function isAppSecretValid(Request $request): bool
    {
        $appSecret = $request->input('app_secret');
        if (! $appSecret) {
            return false;
        }

        $secret = Cache::remember('app_secret_' . config('app.id') . '_' . config('app.kode'), 60, function () {
            return App::where('id', config('app.id'))
                ->where('kode', config('app.kode'))
                ->first()->secret ?? null;
        });

        if (! $secret) {
            return false;
        }

        return $secret === $appSecret;
    }
}
