<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Beasiswa extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function mahasiswas(): BelongsToMany
    {
        return $this->belongsToMany(Mahasiswa::class, 'beasiswa_mahasiswa')
            ->withPivot(['tanggal_penerimaan', 'status'])
            ->withTimestamps();
    }

    public function kategori(): BelongsToMany
    {
        return $this->belongsToMany(Kategori::class, 'beasiswa_kategori')
            ->withTimestamps();
    }
}
