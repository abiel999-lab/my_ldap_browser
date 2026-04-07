<?php

namespace App\Models\Gate;

use Illuminate\Database\Eloquent\Model;

class UserKode extends Model
{
    protected $connection = 'gate';

    protected $table = 'user_kode';
}
