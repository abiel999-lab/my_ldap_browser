<?php

namespace App\Models\Akademik;

use Illuminate\Database\Eloquent\Model;

class PesertaDidik extends Model
{
    protected $connection = 'neosimAkad';

    protected $table = 'akademik.peserta_didik';

    public function mahasiswa()
    {
        return $this->hasMany(Mahasiswa::class, 'peserta_didik_id', 'id');
    }
}
