<?php

namespace App\Filament\Resources\BeasiswaResource\Pages;

use App\Filament\Resources\BeasiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBeasiswa extends ViewRecord
{
    protected static string $resource = BeasiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
