<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pendaftaran extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'berkas_uploaded_json' => 'array',
    ];

    public function periodeBeasiswa()
    {
        return $this->belongsTo(PeriodeBeasiswa::class);
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function berkasPendaftar()
    {
        return $this->hasMany(PendaftaranBerkas::class);
    }
}
