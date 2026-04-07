<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\GateService;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class AuthController extends Controller
{
    public function deauth(Request $request)
    {
        if (Cache::store('redis_auth')->forget($request->input(config('sso.cookie')))) {
            return response()->json([
                'status' => 1,
                'message' => 'Berhasil logout',
            ]);
        } else {
            return response()->json([
                'status' => 0,
                'message' => 'Gagal logout',
            ]);
        }
    }

    public function getLoginAsList()
    {
        $model = (new GateService)->getUserRoleByAppID(config('app.id'));

        return DataTables::of($model)
            ->addColumn('kode', function ($model) {
                return $model['user_kode']['kode'];
            })
            ->addColumn('tipe', function ($model) {
                return $model['tipe']['nama'];
            })
            ->addColumn('nama', function ($model) {
                return $model['nama'];
            })
            ->addColumn('email', function ($model) {
                return $model['email'];
            })
            ->addColumn('role', function ($model) {
                $role = [];
                foreach ($model['user_role'] as $value) {
                    $role[] = $value['role']['nama'];
                }

                return implode(', ', $role);
            })
            ->addColumn('aksi', function ($model) {
                return '<a href="#" onclick="confirmPost(\'' . route('loginas.set', Crypt::encryptString($model['id'])) . '\',\'Login sebagai ' . $model['nama'] . '\')" class="btn btn-primary btn-sm">Login</a>';
            })
            ->addIndexColumn()
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function test() {}

    public function test2()
    {
        User::where('id', 1)->where('email', '1213')->where('email', '1213')->where('email', '1213')->first();
    }
}
