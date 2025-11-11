<?php

namespace App\Filament\Exports;

use App\Models\Pendaftaran;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class PendaftaranExporter extends Exporter
{
    protected static ?string $model = Pendaftaran::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID')
                ->enabledByDefault(false),

            ExportColumn::make('status')
                ->formatStateUsing(fn($state) => $state->getLabel()),

            ExportColumn::make('periodeBeasiswa.beasiswa.nama_beasiswa')
                ->label('Beasiswa'),

            ExportColumn::make('periodeBeasiswa.nama_periode'),

            ExportColumn::make('mahasiswa.user.nim')
                ->label('NIM'),

            ExportColumn::make('mahasiswa.nama')
                ->label('Nama Mahasiswa'),

            ExportColumn::make('mahasiswa.fakultas')
                ->label('Fakultas'),

            ExportColumn::make('mahasiswa.prodi')
                ->label('Program Studi'),

            ExportColumn::make('mahasiswa.angkatan')
                ->label('Angkatan')
                ->enabledByDefault(false),

            ExportColumn::make('mahasiswa.semester')
                ->label('Semester')
                ->enabledByDefault(false),

            ExportColumn::make('mahasiswa.sks')
                ->label('Satuan Kredit Semester')
                ->enabledByDefault(false),

            ExportColumn::make('mahasiswa.ttl_gabungan')
                ->label('Tempat Tanggal Lahir')
                ->enabledByDefault(false),

            ExportColumn::make('mahasiswa.no_hp')
                ->label('No Hp')
                ->enabledByDefault(false),

            ExportColumn::make('mahasiswa.email')
                ->label('Email')
                ->enabledByDefault(false),

            ExportColumn::make('note')
                ->enabledByDefault(false),
            ExportColumn::make('created_at')
                ->enabledByDefault(false),
            ExportColumn::make('updated_at')
                ->enabledByDefault(false),
            ExportColumn::make('deleted_at')
                ->enabledByDefault(false),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your pendaftaran export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
