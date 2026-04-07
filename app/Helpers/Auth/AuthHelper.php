<?php

namespace App\Helpers\Auth;

use App\Helpers\Gate\GateHelper;
use App\Models\Admin\UserUnit;
use App\Models\Ref\Unit;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthHelper
{
    public static function setUser($userData): void
    {
        $user = new User($userData);
        // Suntikkan User ke sistem Auth Laravel
        Auth::setUser($user);
        self::setDefaultCurrentRole();
    }

    public static function setDefaultCurrentRole(): void
    {
        if (! session()->has('current_role')) {
            $defaultRole = Auth::user()->user_role()->first()->role()->kode;
            self::setCurrentRole($defaultRole);
            self::setDefaultUnit();
        }
    }

    public static function setCurrentRole($roleCode): void
    {
        if (Auth::user()->hasRole($roleCode)) {
            session(['current_role' => $roleCode]);
        }
        self::setDefaultUnit();
    }

    public static function getCurrentRole()
    {
        if (Session::has('current_role')) {
            $role = Session::get('current_role');
            if (! Auth::user()->hasRole($role)) {
                self::setDefaultCurrentRole();

                return redirect()->route('filament.app.home');
            }
        } else {
            self::setDefaultCurrentRole();
        }

        return Session::get('current_role');
    }

    public static function isCurrentRole(...$role): bool
    {
        return in_array(self::getCurrentRole(), $role);
    }

    public static function setDefaultUnit()
    {
        $currentRole = self::getCurrentRole();
        $role_pejabat = GateHelper::getRoles('PEJABAT');
        $role_admin_teknis = GateHelper::getRoles('ADMIN_TEKNIS');
        $role_admin_app = GateHelper::getRoles('ADMIN_APP');
        $role_admin_fitur = GateHelper::getRoles('ADMIN_FITUR');
        session(['current_unit' => null]);

        if (in_array($currentRole, $role_pejabat)) { // --------jika pejabat
            $unit_id = UserHelper::getUnitStruktural(Auth::user()->id, $currentRole);
            if (count($unit_id) == 0) { // tidak ada unit struktural
                $pegawai = UserHelper::getPegawai(Auth::user()->kode);
                if ($pegawai) {
                    session(['current_unit' => $pegawai->unit_id]);
                }
            }
            // ---------------cari info unit
            session(['current_unit' => $unit_id[0]]);
        } elseif (in_array($currentRole, $role_admin_teknis) || in_array($currentRole, $role_admin_app) || in_array($currentRole, $role_admin_fitur)) {
            $univ = Unit::where('jenis', '1')->first();
            if ($univ) {
                session(['current_unit' => $univ->id]);
            }
        } elseif ($currentRole == 'KOORDINATOR') {
            $userunit = UserUnit::where('user_id', Auth::user()->id)->first();
            if ($userunit) {
                session(['current_unit' => $userunit->unit_id]);
            }
        } else {
            // ---------------dapatkan unit pegawai yang login sekarang
            $pegawai = UserHelper::getPegawai(Auth::user()->kode);
            if ($pegawai) {
                session(['current_unit' => $pegawai->unit_id]);
            }
        }
    }
}
