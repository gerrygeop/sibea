<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mahasiswa extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function beasiswas(): BelongsToMany
    {
        return $this->belongsToMany(Beasiswa::class, 'beasiswa_mahasiswa')
            ->withPivot(['tanggal_penerimaan', 'status'])
            ->withTimestamps();
    }
}
