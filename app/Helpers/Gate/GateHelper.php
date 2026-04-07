<?php

namespace App\Helpers\Gate;

use App\Models\Gate\Role;
use Illuminate\Support\Facades\Cache;

class GateHelper
{
    public static function getRoles($tipe_kode)
    {
        $cacheKey = 'roles_tipe_' . $tipe_kode;

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $tipeRole = Role::whereHas('tipeRole', function ($query) use ($tipe_kode) {
            $query->where('kode', $tipe_kode);
        })->get()->pluck('kode')->toArray();

        Cache::put($cacheKey, $tipeRole, now()->addHours(4));

        return $tipeRole;

    }
}
