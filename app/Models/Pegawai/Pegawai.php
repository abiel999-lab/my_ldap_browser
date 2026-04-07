<?php

namespace App\Models\Pegawai;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $connection = 'neosimPeg';

    protected $table = 'pegawai';

    public function biodata()
    {
        return $this->belongsTo(Biodata::class, 'biodata_id', 'id');
    }
}
