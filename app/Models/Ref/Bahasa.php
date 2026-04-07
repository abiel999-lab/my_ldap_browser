<?php

namespace App\Models\Ref;

use Illuminate\Database\Eloquent\Model;

class Bahasa extends Model
{
    protected $connection = 'neosimRef';

    protected $table = 'bahasa';

    protected $fillable = [
        'key',
        'value_en',
        'value_id',
        'value_zh',
    ];
}
