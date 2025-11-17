<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodeMahasiswaImport extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $table = 'periode_mahasiswa_imports';
}
