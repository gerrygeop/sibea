<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kategori extends Model
{
    use SoftDeletes;

    protected $table = 'kategoris';

    protected $guarded = [
        'nama_kategori',
        'deskripsi',
    ];

    public function beasiswa(): BelongsToMany
    {
        return $this->belongsToMany(Beasiswa::class, 'beasiswa_kategori')
            ->withTimestamps();
    }
}
