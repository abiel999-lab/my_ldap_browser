<?php

namespace App\Services;

use Illuminate\Http\Client\HttpClientException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GateService
{
    public function getUser($user_id = null)
    {
        $response = Http::retry(3, 100)->get(config('url.service.api.gate') . '/api/user/' . $user_id)->throw();
        if ($response['status'] == 0) {
            throw new HttpClientException($response);
        }

        return $response->collect('data');
    }

    public function storeUser($tipe_user, $nama, $kode, $username, $email, $password, array $role)
    {
        $payload = [
            'tipe_user' => $tipe_user,
            'nama' => $nama,
            'kode' => $kode,
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'role' => $role,
        ];
        $response = Http::retry(3, 100)->post(config('url.service.api.gate') . '/api/user', $payload)->throw();
        if ($response['status'] == 0) {
            throw new HttpClientException($response);
        }

        return $response->collect('data');
    }

    public function syncRoute($app_id, array $route)
    {
        $payload = [
            'app_id' => $app_id,
            'route' => $route,
        ];
        $response = Http::withoutVerifying()->retry(3, 100)->post(config('url.service.api.gate') . '/api/route/sync', $payload)->throw();
        if ($response['status'] == 0) {
            throw new HttpClientException($response);
        }

        return $response->collect('data');
    }

    public function getUserRoleByAppID($app_id)
    {
        if (Cache::store('redis')->has('user_role_' . $app_id)) {
            return Cache::store('redis')->get('user_role_' . $app_id);
        } else {
            $payload = [
                'app_id' => $app_id,
            ];
            $response = Http::withoutVerifying()->retry(3, 100)->get(config('url.service.api.gate') . '/api/user/index/byapp', $payload)->throw();
            // $response = Http::withoutVerifying()->retry(3, 100)->get('http://localhost:8002'.'/api/user/index/byapp',$payload)->throw();
            if ($response['status'] == 0) {
                throw new HttpClientException($response);
            }

            if ($response['status'] == 1) {
                Cache::store('redis')->put('user_role_' . $app_id, $response->collect('data'), 60 * 60);
            } else {
                Cache::store('redis')->forget('user_role_' . $app_id);
            }
        }

        return $response->collect('data');
    }
}
