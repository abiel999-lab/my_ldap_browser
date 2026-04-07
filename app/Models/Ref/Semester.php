<?php

namespace App\Models\Ref;

use App\Models\Akademik\Mahasiswa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;

    protected $connection = 'neosimRef';

    protected $table = 'ref.semester';

    protected static function booted()
    {
        static::addGlobalScope('order', function ($builder) {
            $builder->orderBy('tgl_selesai', 'desc');
        });
    }

    public function smtMasukMahasiswa()
    {
        return $this->hasMany(Mahasiswa::class, 'smt_masuk_id', 'id');
    }
}
