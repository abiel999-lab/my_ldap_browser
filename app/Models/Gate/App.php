<?php

namespace App\Models\Gate;

use Illuminate\Database\Eloquent\Model;

class App extends Model
{
    protected $connection = 'gate';

    protected $table = 'app';
}
