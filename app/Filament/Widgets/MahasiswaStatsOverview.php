<?php

namespace App\Filament\Widgets;

use App\Models\Beasiswa;
use App\Models\Mahasiswa;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class MahasiswaStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    // protected ?string $heading = 'Analytics';

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user->hasAnyRole(['admin', 'staf']);
    }

    protected function getStats(): array
    {
        // Menghitung jumlah total mahasiswa
        $totalMahasiswa = Mahasiswa::count();

        // Menghitung jumlah mahasiswa penerima beasiswa langsung dari tabel pivot
        $penerimaBeasiswa = DB::table('beasiswa_mahasiswa')->distinct('mahasiswa_id')->count();

        // Menghitung mahasiswa yang sedang dalam proses verifikasi
        $verifikasiPending = DB::table('beasiswa_mahasiswa')
            ->where('status', 'menunggu_verifikasi')
            ->count();

        $totalBeasiswa = Beasiswa::count();

        // Membuat array untuk kartu statistik utama
        return [
            Stat::make('Total Mahasiswa', $totalMahasiswa)
                ->description('Jumlah total mahasiswa terdaftar')
                ->color('primary'),

            Stat::make('Total Beasiswa', $totalBeasiswa)
                ->description('Jumlah total beasiswa terdaftar')
                ->color('primary'),

            Stat::make('Penerima Beasiswa', $penerimaBeasiswa)
                ->description('Mahasiswa yang pernah menerima beasiswa')
                ->color('success'),

            Stat::make('Sedang Verifikasi', $verifikasiPending)
                ->description('Pengajuan beasiswa yang belum diverifikasi')
                ->color('warning'),

        ];
    }
}
