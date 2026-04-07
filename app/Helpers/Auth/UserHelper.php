<?php

namespace App\Helpers\Auth;

use App\Models\Pegawai\Pegawai;
use App\Models\Pegawai\StatusAktif;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class UserHelper
{
    public static function getRole(User $user, ?int $app_id = null)
    {
        $user_role = $user->user_role();

        return $user_role;
    }

    public static function hasRole(User $user, int $app_id, int|array $role_kode): bool
    {
        $user_role = $user->user_role();
        foreach ($user_role as $ur) {
            if ($ur->app_id == $app_id) {
                if (is_array($role_kode)) {
                    if (in_array($ur->role->kode, $role_kode)) {
                        $user_role_exists = true;
                        break;
                    }
                } else {
                    if ($ur->role->kode == $role_kode) {
                        $user_role_exists = true;
                        break;
                    }
                }
            }
        }

        return $user_role_exists;
    }

    public static function getUnitStruktural($user_id, $role)
    {
        // ----------------------dapatkan unit struktural
        $unit = [];
        if (Cache::store('redis')->has('unit_struktural_' . $user_id)) {
            $unit = Cache::store('redis')->get('unit_struktural_' . $user_id);
        } else {
            $response = Http::withoutVerifying()->retry(3, 100)->get(config('url.service.api.gate') . '/api/struktural/unit/' . $user_id);
            if ($response['status'] == 1) {
                $unit = $response['data'];
                Cache::store('redis')->put('unit_struktural_' . $user_id, $unit, 60 * 60 * 2);
            }
        }

        if (count($unit) > 0) {
            return $unit[$role] ?? [];
        }

        return [];
    }

    public static function getStruktural($user_id, $role)
    {
        // ----------------------dapatkan struktural
        $struktural = [];
        if (Cache::store('redis')->has('unit_struktural_' . $user_id)) {
            $struktural = Cache::store('redis')->get('struktural_' . $user_id);
        } else {
            $response = Http::withoutVerifying()->retry(3, 100)->get(config('url.service.api.gate') . '/api/struktural/' . $user_id);
            if ($response['status'] == 1) {
                $struktural = $response['data'];
                Cache::store('redis')->put('struktural_' . $user_id, $struktural, 60 * 60 * 2);
            }
        }

        if (count($struktural) > 0) {
            return $struktural[$role] ?? [];
        }

        return [];
    }

    public static function getPegawai($kode)
    {
        $status_aktif = StatusAktif::where('status', 'AK')->get()->pluck('id')->toArray();
        $pegawai = Pegawai::where('nip', $kode)->whereIn('status_aktif_id', $status_aktif)->first();

        return $pegawai;
    }

    public static function userHasRole($role)
    {
        // ------cek current user role
        $current_role = session('current_role');

        // ------cek apakah role sekarang ada di dalam daftar role yang diberikan
        $exist_role = false;
        if (is_array($role)) {
            if (in_array($current_role, $role)) {
                $exist_role = true;
            }
        } else {
            if ($current_role == $role) {
                $exist_role = true;
            }
        }

        if (! $exist_role) { // -------role sekarang tidak ada di dalam daftar role yang diberikan
            return false;
        }

        // ------cek apakah user memiliki role di aplikasi tertentu
        $hasRole = UserHelper::hasRole(auth()->user(), config('app.id'), $role);

        if ($hasRole) {
            return true;
        }

        return false;
    }

    public static function getUserID($email, $type = null)
    {
        $email = strtolower($email);
        $temp = explode('@', $email);
        $email = $temp[0];
        $response = Http::withoutVerifying()->retry(3, 100)->get(config('url.service.api.gate') . '/api/user/email/' . $email . ($type != null ? '/' . $type : ''));

        if ($response->json('status') == '1') {
            $userid = $response->json('data')['id'];

            return [
                'status' => '1',
                'data' => $userid,
            ];
        }

        return [
            'status' => '0',
            'message' => __('message.global.error.not_found'),
        ];
    }

    public static function getRoleId($role)
    {
        $response = Http::withoutVerifying()->retry(3, 100)->get(config('url.service.api.gate') . '/api/role/kode/' . $role);
        if ($response->json('status') == '1') {
            $roleid = $response->json('data')['id'];

            return [
                'status' => '1',
                'data' => $roleid,
            ];
        }

        return [
            'status' => '0',
            'message' => __('message.user_unit.error.role_not_found'),
        ];
    }

    public static function addRole($user_id, $role_id)
    {
        $app_id = config('app.id');

        // ------------cek apakah role sudah ada
        $getrole = Http::withoutVerifying()->retry(3, 100)->get(config('url.service.api.gate') . '/api/user/role', ['user_id' => $user_id, 'app_id' => $app_id, 'role_id' => $role_id]);
        $role = $getrole->json();
        if ($role['status'] == 1) {
            // ------------jika role belum ada, tambahkan
            if ($role['data'] == null) {
                $response = Http::withoutVerifying()->retry(3, 100)->post(config('url.service.api.gate') . '/api/user/role', [
                    'user_id' => $user_id,
                    'role_id' => $role_id,
                    'app_id' => $app_id,
                ]);

                if ($response->json('status') == '1') {
                    return [
                        'status' => '1',
                        'message' => __('message.user_unit.success.role_added'),
                    ];
                }

                return [
                    'status' => '0',
                    'message' => __('message.user_unit.error.role_not_added'),
                ];
            }

            return [
                'status' => '1',
                'message' => __('message.user_unit.success.role_added'),
            ];
        }

        return [
            'status' => '0',
            'message' => __('message.user_unit.error.role_not_added'),
        ];
    }

    public static function deleteRole($user_id, $role_id)
    {
        $app_id = config('app.id');

        // ------------cek apakah role sudah ada
        $response = Http::withoutVerifying()->retry(3, 100)->get(config('url.service.api.gate') . '/api/user/role', ['user_id' => $user_id, 'app_id' => $app_id, 'role_id' => $role_id]);
        $role = $response->json();
        if ($role['status'] == 1) {
            // ------------jika role belum ada, tambahkan
            if ($role['data'] == null) {
                $response = Http::withoutVerifying()->retry(3, 100)->delete(config('url.service.api.gate') . '/api/user/role/' . $role['data']['id']);
                if ($response->json('status') == '1') {
                    return [
                        'status' => '1',
                        'message' => __('message.user_unit.success.role_deleted'),
                    ];
                }

                return [
                    'status' => '0',
                    'message' => __('message.user_unit.error.role_not_deleted'),
                ];
            }
        }

        return [
            'status' => '0',
            'message' => __('message.user_unit.error.role_not_deleted'),
        ];
    }
}
