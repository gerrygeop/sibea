<?php

namespace App\Filament\Resources\PendaftaranResource\Pages;

use App\Enums\StatusPendaftaran;
use App\Enums\UserRole;
use App\Filament\Resources\PendaftaranResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPendaftaran extends EditRecord
{
    protected static string $resource = PendaftaranResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $user = auth()->user();
        $pendaftaran = $this->record;

        if ($user->hasRole(UserRole::MAHASISWA) && in_array($this->record->status, [
            StatusPendaftaran::DRAFT,
            StatusPendaftaran::PERBAIKAN,
        ]) === false) {
            Notification::make()
                ->title('Akses Ditolak')
                ->body('Anda tidak dapat mengedit pendaftaran yang sudah dikirim atau diproses.')
                ->danger()
                ->send();

            $this->redirect($this->getResource()::getUrl('view', ['record' => $pendaftaran]));
        } elseif ($user->hasAnyRole([UserRole::ADMIN, UserRole::STAFF, UserRole::PENGELOLA]) && in_array($this->record->status, [
            StatusPendaftaran::DRAFT,
            StatusPendaftaran::PERBAIKAN,
        ]) === true) {
            Notification::make()
                ->title('Akses Ditolak')
                ->body('Anda tidak dapat mengedit pendaftaran yang masih draft.')
                ->danger()
                ->send();

            $this->redirect($this->getResource()::getUrl('view', ['record' => $pendaftaran]));
        }
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->record;

        // Load semua berkas yang sudah diupload
        $berkasPendaftar = $record->berkasPendaftar;

        // Set data berkas ke form
        foreach ($berkasPendaftar as $berkas) {
            $fieldName = 'berkas_' . $berkas->berkas_wajib_id;
            // ✅ Set file path langsung sebagai string
            $data[$fieldName] = $berkas->file_path;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();
        $periode = $record->periodeBeasiswa;

        foreach ($periode->berkasWajibs as $berkas) {
            $fieldName = 'berkas_' . $berkas->id;
            $fileData = $this->data[$fieldName] ?? null;

            if ($fileData) {
                // Extract file path
                $filePath = is_array($fileData) ? reset($fileData) : $fileData;

                if ($filePath) {
                    $record->berkasPendaftar()->updateOrCreate(
                        [
                            'berkas_wajib_id' => $berkas->id,
                        ],
                        [
                            'file_path' => $filePath,
                        ]
                    );
                }
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn() => $this->record->status === StatusPendaftaran::DRAFT),
            // Actions\ForceDeleteAction::make(),
            // Actions\RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
