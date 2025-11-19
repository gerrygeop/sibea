<?php

namespace App\Jobs;

use App\Models\Mahasiswa;
use App\Models\Pendaftaran;
use App\Models\PeriodeBeasiswa;
use App\Models\User;
use App\Services\ApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportMahasiswaToPeriodeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;
    public $backoff = 10;

    public function __construct(
        public string $nim,
        public int $periodeBeasiswaId,
        public string $status,
        public string $batchId,
        public int $userId
    ) {}

    public function handle(ApiService $apiService): void
    {
        try {
            // Log import attempt
            $importLog = DB::table('periode_mahasiswa_imports')->insertGetId([
                'nim' => $this->nim,
                'periode_beasiswa_id' => $this->periodeBeasiswaId,
                'batch_id' => $this->batchId,
                'status' => 'processing',
                'user_id' => $this->userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Check if user exists
            $user = User::where('nim', $this->nim)->first();

            if (!$user) {
                // Fetch from API
                $biodata = $apiService->getBiodata($this->nim);

                if (!$biodata) {
                    $this->updateLog($importLog, 'failed', 'Data tidak ditemukan di Portal SIAKAD');
                    return;
                }

                // Create user & mahasiswa
                DB::transaction(function () use ($biodata, &$user) {
                    $user = User::create([
                        'name' => $biodata['nama'],
                        'nim' => $this->nim,
                        'password' => bcrypt('password'),
                        'role_id' => 3,
                    ]);

                    Mahasiswa::create([
                        'user_id' => $user->id,
                        'nama' => $biodata['nama'],
                        'email' => $biodata['email'],
                        'tempat_lahir' => $biodata['tempat_lahir'],
                        'tanggal_lahir' => $biodata['tanggal_lahir'],
                        'no_hp' => $biodata['no_hp'],
                        'prodi' => $biodata['program_studi'],
                        'fakultas' => $biodata['fakultas'],
                        'angkatan' => $biodata['angkatan'],
                        'semester' => $biodata['semester'] ?? 0,
                        'sks' => $biodata['sks'] == '' ? 0 : $biodata['sks'],
                        'ip' => $biodata['ip'] == '' ? 0 : $biodata['ip'],
                        'ipk' => $biodata['ipk'] == '' ? 0 : $biodata['ipk'],
                        'status_mahasiswa' => $biodata['status_mahasiswa'],
                    ]);
                });
            }

            // Get mahasiswa
            $mahasiswa = $user->mahasiswa;

            if (!$mahasiswa) {
                $this->updateLog($importLog, 'failed', 'User ditemukan tapi bukan mahasiswa');
                return;
            }

            // Check if already registered
            $existing = Pendaftaran::where('periode_beasiswa_id', $this->periodeBeasiswaId)
                ->where('mahasiswa_id', $mahasiswa->id)
                ->first();

            if ($existing) {
                $this->updateLog($importLog, 'skipped', 'Sudah terdaftar di periode ini');
                return;
            }

            // Pre-check requirements
            $periode = PeriodeBeasiswa::find($this->periodeBeasiswaId);
            $checkResult = $this->checkRequirements($mahasiswa, $periode);

            if (!$checkResult['passed']) {
                $this->updateLog($importLog, 'failed', 'Tidak memenuhi syarat: ' . implode(', ', $checkResult['errors']));
                return;
            }

            // Create pendaftaran
            Pendaftaran::create([
                'periode_beasiswa_id' => $this->periodeBeasiswaId,
                'mahasiswa_id' => $mahasiswa->id,
                'status' => $this->status,
            ]);

            $this->updateLog($importLog, 'success', $mahasiswa->nama);
        } catch (\Exception $e) {
            Log::error('Import mahasiswa to periode failed', [
                'nim' => $this->nim,
                'periode_id' => $this->periodeBeasiswaId,
                'error' => $e->getMessage()
            ]);

            $this->updateLog($importLog ?? null, 'failed', $e->getMessage());
            throw $e;
        }
    }

    private function checkRequirements(Mahasiswa $mahasiswa, PeriodeBeasiswa $periode): array
    {
        $errors = [];

        if (empty($periode->persyaratans_json)) {
            return ['passed' => true, 'errors' => []];
        }

        foreach ($periode->persyaratans_json as $persyaratan) {
            $jenis = $persyaratan['jenis'] ?? null;
            $nilai = $persyaratan['nilai'] ?? null;
            $keterangan = $persyaratan['keterangan'] ?? null;

            switch ($jenis) {
                case 'IPK':
                    if ($keterangan === 'Minimal' && $mahasiswa->ipk < floatval($nilai)) {
                        $errors[] = "IPK {$mahasiswa->ipk} < {$nilai}";
                    } elseif ($keterangan === 'Maksimal' && $mahasiswa->ipk > floatval($nilai)) {
                        $errors[] = "IPK {$mahasiswa->ipk} > {$nilai}";
                    }
                    break;
                case 'Semester':
                    if ($keterangan === 'Minimal' && $mahasiswa->semester < intval($nilai)) {
                        $errors[] = "Semester {$mahasiswa->semester} < {$nilai}";
                    } elseif ($keterangan === 'Maksimal' && $mahasiswa->semester > intval($nilai)) {
                        $errors[] = "Semester {$mahasiswa->semester} > {$nilai}";
                    }
                    break;
                case 'SKS':
                    if ($keterangan === 'Minimal' && $mahasiswa->sks < intval($nilai)) {
                        $errors[] = "SKS {$mahasiswa->sks} < {$nilai}";
                    } elseif ($keterangan === 'Maksimal' && $mahasiswa->sks > intval($nilai)) {
                        $errors[] = "SKS {$mahasiswa->sks} > {$nilai}";
                    }
                    break;
            }
        }

        return [
            'passed' => empty($errors),
            'errors' => $errors
        ];
    }

    private function updateLog(?int $logId, string $status, string $message): void
    {
        if (!$logId) return;

        DB::table('periode_mahasiswa_imports')
            ->where('id', $logId)
            ->update([
                'status' => $status,
                'message' => $message,
                'processed_at' => now(),
                'updated_at' => now(),
            ]);
    }
}
