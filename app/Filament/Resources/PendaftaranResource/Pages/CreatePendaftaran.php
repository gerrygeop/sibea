<?php

namespace App\Filament\Resources\PendaftaranResource\Pages;

use App\Enums\StatusPendaftaran;
use App\Enums\UserRole;
use App\Filament\Resources\PendaftaranResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreatePendaftaran extends CreateRecord
{
    protected static string $resource = PendaftaranResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        if ($user->hasRole(UserRole::MAHASISWA)) {
            $periodeBeasiswaId = $data['periode_beasiswa_id'] ?? request()->get('periode_beasiswa_id');

            if (!$periodeBeasiswaId) {
                Notification::make()
                    ->title('Error')
                    ->body('Periode beasiswa tidak ditemukan.')
                    ->danger()
                    ->persistent()
                    ->send();

                $this->halt();
            }

            $data['periode_beasiswa_id'] = $periodeBeasiswaId;
            $data['mahasiswa_id'] = $user->mahasiswa->id;
            $data['status'] = StatusPendaftaran::DRAFT->value;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->getRecord();
        $periode = $record->periodeBeasiswa;

        // Loop berdasarkan berkas wajib dari periode
        foreach ($periode->berkasWajibs as $berkas) {
            $fieldName = 'berkas_' . $berkas->id;
            $fileData = $this->data[$fieldName] ?? null;

            if ($fileData) {
                // Extract file path
                $filePath = is_array($fileData) ? reset($fileData) : $fileData;

                if ($filePath) {
                    $record->berkasPendaftar()->create([
                        'berkas_wajib_id' => $berkas->id,
                        'file_path' => $filePath,
                    ]);
                }
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return Actions\Action::make('createAnother')
            ->hidden();
    }

    protected function getCreateFormAction(): Action
    {
        $action = parent::getCreateFormAction();

        $action->label('Simpan');

        return $action;
    }
}
