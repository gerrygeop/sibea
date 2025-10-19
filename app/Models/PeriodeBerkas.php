<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodeBerkas extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $table = 'periode_berkas';

    public function periodeBeasiswa()
    {
        return $this->belongsTo(PeriodeBeasiswa::class, 'periode_beasiswa_id');
    }

    public function berkasWajib()
    {
        return $this->belongsTo(BerkasWajib::class, 'berkas_wajib_id');
    }
}
