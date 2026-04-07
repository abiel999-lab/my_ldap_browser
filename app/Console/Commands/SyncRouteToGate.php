<?php

namespace App\Console\Commands;

use App\Services\GateService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Log;

class SyncRouteToGate extends Command
{
    /**
     * URI yang tidak perlu didaftarkan ke Gate.
     */
    private $excludeUri = [
        '_ignition/health-check',
        '_ignition/execute-solution',
        '_ignition/update-config',
        'dev/{test?}',
    ];

    /**
     * Middleware untuk menentukan apakah route adalah route API.
     */
    private $apiMiddleware = [
        'api',
    ];

    /**
     * Semua HTTP Method yang ada di Project.
     */
    private $availableMethod = ['GET', 'POST', 'PUT', 'DELETE'];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'route:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Project Route to Gate';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $routes = Route::getRoutes()->get();
        $availableMethod = collect($this->availableMethod);
        $requestRoute = [];

        foreach ($routes as $r) {
            if (in_array($r->uri(), $this->excludeUri) || is_null($r->getName())) {
                continue;
            }

            $is_api = count(array_intersect($this->apiMiddleware, $r->gatherMiddleware()));

            $acceptedMethod = array_intersect($this->availableMethod, $r->methods());
            foreach ($acceptedMethod as $method) {
                $route = strtolower($r->getName());
                $name = Str::title(Str::replace('.', ' ', $route));
                $path = Str::start(strtolower($r->uri()), '/');
                $route = [
                    'nama' => $name,
                    'route' => $route,
                    'path' => $path,
                    'method' => $method,
                    'is_api' => (bool) $is_api,
                    'deskripsi' => null,
                ];
                array_push($requestRoute, $route);
            }
        }

        $response = (new GateService)->syncRoute(config('app.id'), $requestRoute);

        Log::info('Sync Route:', $response->toArray());
        $this->line('Sinkronisasi Route ke Gate berhasil.');

        return 0;
    }
}
