<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BerkasWajib extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_berkas',
        'deskripsi',
    ];

    protected $table = 'berkas_wajibs';

    public function periodeBeasiswas()
    {
        return $this->belongsToMany(PeriodeBeasiswa::class, 'periode_berkas')->withTimestamps();
    }

    public function pendaftaranBerkas()
    {
        return $this->hasMany(PendaftaranBerkas::class);
    }
}
