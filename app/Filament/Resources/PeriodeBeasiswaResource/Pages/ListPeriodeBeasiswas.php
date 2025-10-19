<?php

namespace App\Filament\Resources\PeriodeBeasiswaResource\Pages;

use App\Filament\Resources\PeriodeBeasiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPeriodeBeasiswas extends ListRecords
{
    protected static string $resource = PeriodeBeasiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
