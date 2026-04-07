<?php

namespace App\Models\Gate;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $connection = 'gate';

    protected $table = 'role';

    public function appRoles()
    {
        return $this->hasMany(AppRole::class, 'role_id');
    }

    public function tipeRole()
    {
        return $this->belongsTo(TipeRole::class, 'tipe_role_id');
    }
}
