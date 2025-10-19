<?php

namespace App\Filament\Resources\PeriodeBeasiswaResource\Pages;

use App\Filament\Resources\PeriodeBeasiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPeriodeBeasiswa extends EditRecord
{
    protected static string $resource = PeriodeBeasiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
