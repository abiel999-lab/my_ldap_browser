<?php

namespace App\Models\Gate;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $connection = 'gate';

    protected $table = 'user_role';
}
