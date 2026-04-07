<?php

namespace App\Models\Gate;

use Illuminate\Database\Eloquent\Model;

class TipeRole extends Model
{
    protected $connection = 'gate';

    protected $table = 'tipe_role';

    public function role()
    {
        return $this->hasMany(Role::class, 'tipe_role_id', 'id');
    }
}
