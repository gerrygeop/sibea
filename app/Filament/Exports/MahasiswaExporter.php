<?php

namespace App\Filament\Exports;

use App\Models\Mahasiswa;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Style;

class MahasiswaExporter extends Exporter
{
    protected static ?string $model = Mahasiswa::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID')
                ->enabledByDefault(false),
            ExportColumn::make('user.nim')
                ->label('NIM'),
            ExportColumn::make('nama'),
            ExportColumn::make('email'),
            ExportColumn::make('ttl')
                ->label('Tempat, Tanggal Lahir'),
            ExportColumn::make('no_hp'),
            ExportColumn::make('prodi'),
            ExportColumn::make('fakultas'),
            ExportColumn::make('angkatan'),
            ExportColumn::make('sks')
                ->label('SKS'),
            ExportColumn::make('semester'),
            ExportColumn::make('ip')
                ->label('IP'),
            ExportColumn::make('ipk')
                ->label('IPK'),
            ExportColumn::make('beasiswa.nama_beasiswa')
                ->label('Nama Beasiswa')
                ->enabledByDefault(false),
            ExportColumn::make('beasiswa.lemabaga_penyelenggara')
                ->enabledByDefault(false),
            ExportColumn::make('beasiswa.besar_beasiswa')
                ->enabledByDefault(false),
            ExportColumn::make('beasiswa.periode')
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
        $body = 'Your mahasiswa export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

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
