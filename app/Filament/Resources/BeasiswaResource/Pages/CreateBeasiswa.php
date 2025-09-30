<?php

namespace App\Filament\Resources\BeasiswaResource\Pages;

use App\Filament\Resources\BeasiswaResource;
use App\Models\Beasiswa;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateBeasiswa extends CreateRecord
{
    protected static string $resource = BeasiswaResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        try {
            return DB::transaction(function () use ($data) {


                // 1. Dapatkan user yang sedang login
                $user = auth()->user();

                if ($user->hasAnyRole(['admin', 'staf'])) {
                    // Jika user adalah admin atau superadmin, buat beasiswa tanpa tautan ke mahasiswa
                    return static::getModel()::create($data);
                }

                // 2. Temukan data mahasiswa yang berelasi dengan user
                // Asumsi ada relasi hasOne di model User ke model Mahasiswa
                $mahasiswa = $user->mahasiswa;

                // 3. Buat record Beasiswa baru dengan data dari form
                $beasiswa = Beasiswa::create($data);

                // 4. Lampirkan (attach) beasiswa yang baru dibuat ke mahasiswa yang sedang login
                // Gunakan metode attach() dari relasi many-to-many
                $mahasiswa->beasiswas()->attach($beasiswa->id, [
                    // Set nilai untuk kolom pivot table beasiswa_mahasiswa
                    'tanggal_penerimaan' => now(),
                ]);

                // Kembalikan model beasiswa yang baru dibuat
                return $beasiswa;
            });
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
