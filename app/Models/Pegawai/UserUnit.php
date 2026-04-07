<?php

namespace App\Models\Pegawai;

use App\Models\Ref\Unit;
use Illuminate\Database\Eloquent\Model;

class UserUnit extends Model
{
    protected $connection = 'neosimPeg';

    protected $table = 'user_unit';

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }
}
