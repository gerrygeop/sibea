<?php

namespace App\Filament\Exports;

use App\Models\Beasiswa;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Style;

class BeasiswaExporter extends Exporter
{
    protected static ?string $model = Beasiswa::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID')
                ->enabledByDefault(false),
            ExportColumn::make('nama_beasiswa'),
            ExportColumn::make('lembaga_penyelenggara'),
            ExportColumn::make('besar_beasiswa'),
            ExportColumn::make('periode'),
            ExportColumn::make('deskripsi'),
            ExportColumn::make('mahasiswas.nama')
                ->label('Nama Mahasiswa'),
            ExportColumn::make('mahasiswas.email')
                ->label('Email Mahasiswa'),
            ExportColumn::make('mahasiswas.prodi')
                ->label('Prodi Mahasiswa'),
            ExportColumn::make('mahasiswas.fakultas')
                ->label('fakultas Mahasiswa'),
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
        $body = 'Your beasiswa export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }

    public function getXlsxHeaderCellStyle(): ?Style
    {
        return (new Style())
            ->setFontBold()
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER);
    }
}
