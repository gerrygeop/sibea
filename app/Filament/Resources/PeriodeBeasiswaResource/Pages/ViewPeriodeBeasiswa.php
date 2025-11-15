<?php

namespace App\Filament\Resources\PeriodeBeasiswaResource\Pages;

use App\Filament\Resources\PendaftaranResource;
use App\Filament\Resources\PeriodeBeasiswaResource;
use App\Models\Pendaftaran;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewPeriodeBeasiswa extends ViewRecord
{
    protected static string $resource = PeriodeBeasiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('daftar')
                ->label('Daftar')
                ->action(function () {
                    $periode = $this->record;
                    $mahasiswa = auth()->user()->mahasiswa;

                    // Pre-check persyaratan
                    if (!empty($periode->persyaratans_json) && is_array($periode->persyaratans_json)) {
                        $errors = [];

                        foreach ($periode->persyaratans_json as $persyaratan) {
                            $jenis = $persyaratan['jenis'] ?? null;
                            $nilai = $persyaratan['nilai'] ?? null;
                            $keterangan = $persyaratan['keterangan'] ?? null;

                            switch ($jenis) {
                                case 'IPK':
                                    if ($keterangan === 'Minimal' && $mahasiswa->ipk < floatval($nilai)) {
                                        $errors[] = "IPK Anda ({$mahasiswa->ipk}) tidak memenuhi persyaratan minimal {$nilai}.";
                                    } elseif ($keterangan === 'Maksimal' && $mahasiswa->ipk > floatval($nilai)) {
                                        $errors[] = "IPK Anda ({$mahasiswa->ipk}) melebihi persyaratan maksimal {$nilai}.";
                                    }
                                    break;
                                case 'Semester':
                                    if ($keterangan === 'Minimal' && $mahasiswa->semester < intval($nilai)) {
                                        $errors[] = "Semester Anda ({$mahasiswa->semester}) tidak memenuhi persyaratan minimal {$nilai}.";
                                    } elseif ($keterangan === 'Maksimal' && $mahasiswa->semester > intval($nilai)) {
                                        $errors[] = "Semester Anda ({$mahasiswa->semester}) melebihi persyaratan maksimal {$nilai}.";
                                    }
                                    break;
                                case 'SKS':
                                    if ($keterangan === 'Minimal' && $mahasiswa->sks < intval($nilai)) {
                                        $errors[] = "SKS Anda ({$mahasiswa->sks}) tidak memenuhi persyaratan minimal {$nilai}.";
                                    } elseif ($keterangan === 'Maksimal' && $mahasiswa->sks > intval($nilai)) {
                                        $errors[] = "SKS Anda ({$mahasiswa->sks}) melebihi persyaratan maksimal {$nilai}.";
                                    }
                                    break;
                            }
                        }

                        if (!empty($errors)) {
                            Notification::make()
                                ->title('Tidak memenuhi persyaratan pendaftaran')
                                ->body(implode('<br>', $errors))
                                ->danger()
                                ->persistent()
                                ->send();
                            return;
                        }
                    }

                    $this->redirect(PendaftaranResource::getUrl('create', [
                        'periode_beasiswa_id' => $this->record->id,
                    ]));
                })
                ->visible(function () {
                    $user = auth()->user();
                    if (!$user->hasRole('mahasiswa')) {
                        return false;
                    }

                    $mahasiswa = $user->mahasiswa;
                    $periode = $this->record;

                    $sudahMendaftar = Pendaftaran::where('mahasiswa_id', $mahasiswa->id)
                        ->where('periode_beasiswa_id', $periode->id)
                        ->exists();

                    $today = now()->startOfDay();

                    $tanggalMulai = \Carbon\Carbon::parse($periode->tanggal_mulai_daftar)->startOfDay();
                    $tanggalAkhir = \Carbon\Carbon::parse($periode->tanggal_akhir_daftar)->endOfDay();

                    $dalamTanggal = $today->greaterThanOrEqualTo($tanggalMulai)
                        && $today->lessThanOrEqualTo($tanggalAkhir);

                    return !$sudahMendaftar && $dalamTanggal && $periode->is_aktif;
                }),
        ];
    }
}
