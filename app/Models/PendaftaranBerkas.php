<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendaftaranBerkas extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $table = 'pendaftaran_berkas';

    public function pendaftaran()
    {
        return $this->belongsTo(Pendaftaran::class);
    }

    public function berkasWajib()
    {
        return $this->belongsTo(BerkasWajib::class);
    }
}
