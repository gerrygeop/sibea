<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PeriodeBeasiswa extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'is_aktif' => 'boolean',
        'persyaratans_json' => 'array',
        'berkas_wajibs_json' => 'array',
    ];

    public function beasiswa()
    {
        return $this->belongsTo(Beasiswa::class);
    }

    public function pendaftarans()
    {
        return $this->hasMany(Pendaftaran::class);
    }

    public function berkasWajibs()
    {
        return $this->belongsToMany(BerkasWajib::class, 'periode_berkas')->withTimestamps();
    }

    public function periodeBerkas()
    {
        return $this->hasMany(PeriodeBerkas::class);
    }
}
