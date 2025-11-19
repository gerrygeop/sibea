<?php

namespace App\Jobs;

use App\Models\Mahasiswa;
use App\Models\User;
use App\Services\ApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportMahasiswaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;
    public $backoff = 10;

    public function __construct(
        public string $nim,
        public string $batchId,
        public int $userId
    ) {}

    public function handle(ApiService $apiService): void
    {
        try {
            // Log import attempt
            $importLog = DB::table('mahasiswa_imports')->insertGetId([
                'nim' => $this->nim,
                'batch_id' => $this->batchId,
                'status' => 'processing',
                'user_id' => $this->userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $user = User::where('nim', $this->nim)->first();

            if (!$user) {
                $biodata = $apiService->getBiodata($this->nim);

                if (!$biodata) {
                    $this->updateLog($importLog, 'failed', 'Data tidak ditemukan di Portal SIAKAD');
                    return;
                }

                DB::transaction(function () use ($biodata, &$user) {
                    $user = User::create([
                        'name' => $biodata['nama'],
                        'nim' => $this->nim,
                        'password' => bcrypt('password'),
                        'role_id' => 3
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

            $mahasiswa = $user->mahasiswa;

            if (!$mahasiswa) {
                $this->updateLog($importLog, 'failed', 'User ditemukan tapi bukan mahasiswa');
                return;
            }

            $this->updateLog($importLog, 'success', $mahasiswa->nama);
        } catch (\Exception $e) {
            Log::error('Import mahasiswa failed', [
                'nim' => $this->nim,
                'error' => $e->getMessage()
            ]);

            $this->updateLog($importLog ?? null, 'failed', $e->getMessage());
            throw $e;
        }
    }

    private function updateLog(?int $logId, string $status, string $message): void
    {
        if (!$logId) return;

        DB::table('mahasiswa_imports')
            ->where('id', $logId)
            ->update([
                'status' => $status,
                'message' => $message,
                'processed_at' => now(),
                'updated_at' => now(),
            ]);
    }
}
