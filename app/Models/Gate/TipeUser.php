<?php

namespace App\Models\Gate;

use Illuminate\Database\Eloquent\Model;

class TipeUser extends Model
{
    protected $connection = 'gate';

    protected $table = 'tipe_user';
}
