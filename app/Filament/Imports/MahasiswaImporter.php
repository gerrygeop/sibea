<?php

namespace App\Filament\Imports;

use App\Models\Mahasiswa;
use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class MahasiswaImporter extends Importer
{
    protected static ?string $model = Mahasiswa::class;

    protected array $processedNims = [];
    protected array $processedEmails = [];

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('nim')
                ->label('NIM')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255'])
                ->example('2011102441001')
                ->fillRecordUsing(fn() => null),

            ImportColumn::make('nama')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255'])
                ->example('John Doe'),

            ImportColumn::make('email')
                ->requiredMapping()
                ->rules(['required', 'email', 'max:255'])
                ->example('john@example.com'),

            ImportColumn::make('tempat_lahir')
                ->requiredMapping()
                ->rules(['required', 'string'])
                ->example('Jakarta'),

            ImportColumn::make('tanggal_lahir')
                ->requiredMapping()
                ->rules(['required', 'date'])
                ->example('2000-01-01'),

            ImportColumn::make('no_hp')
                ->requiredMapping()
                ->rules(['required', 'string'])
                ->example('081234567890'),

            ImportColumn::make('prodi')
                ->label('Program Studi')
                ->requiredMapping()
                ->rules(['required', 'string'])
                ->example('Sistem Informasi'),

            ImportColumn::make('fakultas')
                ->requiredMapping()
                ->rules(['required', 'string'])
                ->example('Fakultas Ushuluddin Adab dan Dakwah'),

            ImportColumn::make('angkatan')
                ->requiredMapping()
                ->rules(['required', 'numeric'])
                ->example('2020'),

            ImportColumn::make('sks')
                ->label('SKS')
                ->requiredMapping()
                ->rules(['required', 'numeric', 'min:0'])
                ->example('144'),

            ImportColumn::make('semester')
                ->requiredMapping()
                ->rules(['required', 'numeric', 'min:1', 'max:14'])
                ->example('5'),

            ImportColumn::make('ip')
                ->label('IP')
                ->requiredMapping()
                ->rules(['required', 'numeric', 'min:0', 'max:4'])
                ->example('3.50'),

            ImportColumn::make('ipk')
                ->label('IPK')
                ->requiredMapping()
                ->rules(['required', 'numeric', 'min:0', 'max:4'])
                ->example('3.45'),

            ImportColumn::make('status_mahasiswa')
                ->rules(['nullable', 'string', 'in:Aktif,Cuti,Lulus,Drop Out'])
                ->example('Aktif'),
        ];
    }

    public function resolveRecord(): ?Mahasiswa
    {
        $nim = $this->data['nim'];
        $email = $this->data['email'];
        $password = 'password';

        if (in_array($nim, $this->processedNims)) {
            throw new \Exception("NIM '{$nim}' terdeteksi duplikat di dalam file impor. Baris dilewati.");
        }
        if (in_array($email, $this->processedEmails)) {
            throw new \Exception("Email '{$email}' terdeteksi duplikat di dalam file impor. Baris dilewati.");
        }

        // Tandai NIM dan Email sudah diproses
        $this->processedNims[] = $nim;
        $this->processedEmails[] = $email;

        try {
            return DB::transaction(function () use ($nim, $email, $password) {
                $existingMahasiswaByEmail = Mahasiswa::where('email', $email)->first();

                // Cek apakah user dengan NIM sudah ada
                $user = User::where('nim', $nim)->first();

                // Skenario 1: User (NIM) sudah ada di DB
                if ($user) {
                    // Skenario 1.1: Cek konflik email
                    if ($existingMahasiswaByEmail && $existingMahasiswaByEmail->user_id !== $user->id) {
                        // Konflik: Email sudah dipakai orang lain di DB
                        throw new \Exception("Email '{$email}' sudah digunakan oleh mahasiswa lain ({$existingMahasiswaByEmail->nama}). Baris dilewati.");
                    }

                    // Update data User
                    $user->update([
                        'name' => $this->data['nama'],
                        // 'password' => Hash::make($password), // Reset password
                    ]);
                }
                // Skenario 2: User (NIM) baru
                else {
                    // Skenario 2.1: Cek konflik email
                    if ($existingMahasiswaByEmail) {
                        // Konflik: Email sudah ada, tapi NIM baru
                        throw new \Exception("Email '{$email}' sudah digunakan ({$existingMahasiswaByEmail->nama}), tidak bisa membuat NIM '{$nim}' baru. Baris dilewati.");
                    }

                    // Buat User baru
                    $user = User::create([
                        'name' => $this->data['nama'],
                        'nim' => $nim,
                        'password' => Hash::make($password),
                        'role_id' => 3, // Role mahasiswa (Asumsi role_id 3 adalah Mahasiswa)
                    ]);
                }

                // --- Proses Mahasiswa ---
                // Setelah User dibuat/diupdate, kita updateOrCreate Mahasiswa
                $mahasiswaData = [
                    'nama' => $this->data['nama'],
                    'email' => $this->data['email'],
                    'tempat_lahir' => $this->data['tempat_lahir'],
                    'tanggal_lahir' => $this->data['tanggal_lahir'],
                    'no_hp' => $this->data['no_hp'],
                    'prodi' => $this->data['prodi'],
                    'fakultas' => $this->data['fakultas'],
                    'angkatan' => $this->data['angkatan'],
                    'sks' => $this->data['sks'],
                    'semester' => $this->data['semester'],
                    'ip' => $this->data['ip'],
                    'ipk' => $this->data['ipk'],
                    'status_mahasiswa' => $this->data['status_mahasiswa'] ?? 'Aktif',
                ];

                // updateOrCreate akan mencari berdasarkan user_id
                $mahasiswa = Mahasiswa::updateOrCreate(
                    ['user_id' => $user->id],
                    $mahasiswaData
                );

                return $mahasiswa;
            });
        } catch (\Exception $e) {
            // Log error untuk debugging
            Log::error('Import Mahasiswa Gagal (Exception): ' . $e->getMessage(), ['nim' => $nim]);

            throw new \Exception('exception', $e->getMessage());
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your mahasiswa import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
