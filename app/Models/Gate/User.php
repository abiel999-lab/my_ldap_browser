<?php

namespace App\Models\Gate;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $connection = 'gate';

    protected $table = 'user';

    public function user_role()
    {
        return $this->hasMany(UserRole::class, 'user_id')->orderBy('role_id');
    }

    public function tipe()
    {
        return $this->belongsTo(TipeUser::class, 'tipe_user_id');
    }

    public function user_kode()
    {
        return $this->hasOne(UserKode::class, 'user_id');
    }

    public function scopePegawai($query)
    {
        return $query->where('tipe_user_id', 1);
    }

    public function scopeMahasiswa($query)
    {
        return $query->where('tipe_user_id', 2);
    }

    public function scopeUmum($query)
    {
        return $query->where('tipe_user_id', 3);
    }

    public function scopeAktif($query)
    {
        return $query->where('is_aktif', 1);
    }
}
