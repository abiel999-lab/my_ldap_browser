<?php

namespace App\Models\Ref;

use App\Helpers\Auth\UserHelper;
use App\Helpers\Gate\GateHelper;
use App\Models\Admin\UserUnit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Unit extends Model
{
    use HasFactory;

    protected $connection = 'neosimRef';

    protected $table = 'ref.unit';

    protected $appends = ['fakultas_id'];

    protected function getFakultasIdAttribute()
    {
        if ($this->is_akademik != 1) {
            return null;
        }
        if ($this->jenis == 2) {
            return $this->id;
        } else {
            $parent = $this;
            $jenis = $parent->jenis;
            do {
                $parent = $parent->parent()->first();
                $jenis = $parent->jenis;
                if ($jenis == 2) {
                    return $parent->id;
                }
            } while ($jenis > 2);
        }

        return null;
    }

    public function user_unit()
    {
        return $this->hasMany(UserUnit::class, 'unit_id', 'id');
    }

    protected static function booted()
    {
        static::addGlobalScope('order', function ($builder) {
            $builder->orderBy('urutan', 'asc');
        });
    }

    public function scopeNama($query)
    {
        return $query->selectRaw("*,REPEAT('  ', level) || nama AS nama");
    }

    public function scopeUnitUtama($query, $jenis = null)
    {
        $listJenis = ['1', '2', '3', '4', '6', '9'];
        if ($jenis != null) {
            if ($jenis == 'UA') {
                $listJenis = ['1', '2', '3', '4', '9'];
            } elseif ($jenis == 'UP') {
                $listJenis = ['1', '6'];
            }
        }

        return $query->where('is_aktif', '1')
            ->whereIn('jenis', $listJenis);
    }

    public function scopeUserUnit($query)
    {
        // ---------dapatkan role saat ini
        $current_role = session('current_role');
        $role_pejabat = GateHelper::getRoles('PEJABAT');
        $role_admin_teknis = GateHelper::getRoles('ADMIN_TEKNIS');
        $role_admin_app = GateHelper::getRoles('ADMIN_APP');
        $role_admin_fitur = GateHelper::getRoles('ADMIN_FITUR');
        $role_subadmin = GateHelper::getRoles('SUBADMIN');

        if (in_array($current_role, $role_pejabat)) { // --------jika pejabat
            $unit_id = UserHelper::getUnitStruktural(Auth::user()->id, $current_role);
            if (count($unit_id) == 0) {
                return $query->where('id', 0);
            }
            // ---------------cari info unit
            $unit = Unit::whereIn('id', $unit_id)->get();

            return $query->where(function ($q) use ($unit) {
                foreach ($unit as $u) {
                    $q = $q->orWhere('info_left', '>=', $u->info_left)->where('info_right', '<=', $u->info_right);
                }
            });
        } elseif (in_array($current_role, $role_admin_teknis) || in_array($current_role, $role_admin_app) || in_array($current_role, $role_admin_fitur)) {
            // jika admin teknis, boleh akses semua unit
            return $query;
        } elseif (in_array($current_role, $role_subadmin)) {
            $userunit = UserUnit::where('user_id', Auth::user()->id)->get();
            $unit = Unit::whereIn('id', $userunit->pluck('unit_id')->toArray())->get();

            return $query->where(function ($q) use ($unit) {
                foreach ($unit as $u) {
                    $q = $q->orWhere('info_left', '>=', $u->info_left)->where('info_right', '<=', $u->info_right);
                }
            });
        }

        // ---------------jika egawai biasa
        $pegawai = UserHelper::getPegawai(Auth::user()->kode);
        $listUnit = [$pegawai->unit_id];

        return $query->whereIn('id', $listUnit);
    }
}
