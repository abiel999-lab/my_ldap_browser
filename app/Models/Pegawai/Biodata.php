<?php

namespace App\Models\Pegawai;

use Illuminate\Database\Eloquent\Model;

class Biodata extends Model
{
    protected $connection = 'neosimPeg';

    protected $table = 'pegawai';

    public function pegawai()
    {
        return $this->hasMany(Pegawai::class, 'biodata_id', 'id');
    }
}
