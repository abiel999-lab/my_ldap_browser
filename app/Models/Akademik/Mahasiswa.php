<?php

namespace App\Models\Akademik;

use App\Models\Ref\Semester;
use App\Models\Ref\Unit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $connection = 'neosimAkad';

    protected $table = 'akademik.mahasiswa';

    protected $fillable = [
        'nrp',
        'email',
        'peserta_didik_id',
        'attachment',
        'attachment_file_names',
    ];

    public function pesertaDidik()
    {
        return $this->belongsTo(PesertaDidik::class, 'peserta_didik_id', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'program_studi_id', 'id');
    }

    protected static function booted()
    {
        static::addGlobalScope('order', function ($builder) {
            $builder->orderBy('nrp', 'desc');
        });
    }

    public function smtMasukMahasiswa()
    {
        return $this->belongsTo(Semester::class, 'smt_masuk_id', 'id');
    }
}
