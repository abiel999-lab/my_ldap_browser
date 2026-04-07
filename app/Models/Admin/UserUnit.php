<?php

namespace App\Models\Admin;

use App\Models\Gate\Role;
use App\Models\Gate\User;
use App\Models\Ref\Unit;
use App\Traits\AutoCreateUpdateBy;
use Illuminate\Database\Eloquent\Model;

class UserUnit extends Model
{
    use AutoCreateUpdateBy;

    protected $table = 'user_unit';

    protected $fillable = [
        'user_id',
        'unit_id',
        'role_id',
        'created_by',
        'updated_by',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }
}
