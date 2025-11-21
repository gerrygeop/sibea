<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PeriodeBeasiswa extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_mulai_daftar' => 'date',
        'tanggal_akhir_daftar' => 'date',
        'is_aktif' => 'boolean',
        'persyaratans_json' => 'array',
    ];

    protected function isAktif(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                // Konversi tanggal DB ke objek Carbon
                $tanggalMulai = Carbon::parse($attributes['tanggal_mulai_daftar']);
                $tanggalAkhir = Carbon::parse($attributes['tanggal_akhir_daftar']);
                $sekarang = Carbon::now();

                // #1. Pengecekan Kapan Harus Nonaktif (Tanggal Akhir Sudah Lewat)
                if ($sekarang->greaterThan($tanggalAkhir->endOfDay())) {
                    return false;
                }

                // #2. Pengecekan Kapan Harus Otomatis Aktif
                // Jika SEKARANG >= TANGGAL MULAI dan belum lewat TANGGAL AKHIR
                // DAN di database tercatat FALSE (belum pernah diaktifkan)
                if ($sekarang->greaterThanOrEqualTo($tanggalMulai) && (bool) $value === false) {
                    return true;
                }

                // #3. Pengecekan Admin Override dan Status Aktif Normal
                // Di luar kondisi #1 dan #2, kita ikuti nilai dari database ($value).
                // Jika sudah aktif (TRUE) dan admin menonaktifkan (FALSE), nilai DB akan dipertahankan.
                return (bool) $value;
            },
        );
    }

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

    public function pengelola()
    {
        return $this->belongsToMany(User::class, 'periode_beasiswa_pengelola')->withTimestamps();
    }
}
