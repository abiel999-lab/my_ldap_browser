<?php

namespace App\Models\Pegawai;

use Illuminate\Database\Eloquent\Model;

class StatusAktif extends Model
{
    protected $connection = 'neosimPeg';

    protected $table = 'status_aktif';
}
