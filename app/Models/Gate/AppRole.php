<?php

namespace App\Models\Gate;

use Illuminate\Database\Eloquent\Model;

class AppRole extends Model
{
    protected $connection = 'gate';

    protected $table = 'app_role';

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
