<?php

namespace App\Filament\Resources\PendaftaranResource\Pages;

use App\Filament\Resources\PendaftaranResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewPendaftaran extends ViewRecord
{
    protected static string $resource = PendaftaranResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $actions = [];

        // Aksi untuk mahasiswa
        if ($user->hasRole('mahasiswa')) {
            $actions[] = Actions\EditAction::make()
                ->visible(fn() => $this->record->status === 'draft');

            $actions[] = Actions\Action::make('kirim')
                ->label('Kirim Pendaftaran')
                ->icon('heroicon-o-paper-airplane')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Pendaftaran')
                ->modalDescription('Setelah dikirim anda tidak dapat mengedit pendaftaran ini lagi. Pastikan semua data dan berkas sudah benar.')
                ->modalSubmitActionLabel('Ya, Kirim Pendaftaran')
                ->modalCancelActionLabel('Batal')
                ->color('success')
                ->action(function () {
                    $record = $this->record;

                    $jumlahBerkas = $record->berkasPendaftar()->count();
                    if ($jumlahBerkas === 0) {
                        Notification::make()
                            ->title('Gagal Mengirim')
                            ->body('Anda belum mengupload berkas apapun. Silakan edit pendaftaran dan upload berkas terlebih dahulu.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $record->update([
                        'status' => 'verifikasi',
                    ]);

                    Notification::make()
                        ->title('Pendaftaran Terkirim!')
                        ->body('Pendaftaran Anda telah dikirim untuk diverifikasi. Silakan tunggu hasil verifikasi dari admin.')
                        ->success()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('view', ['record' => $record]));
                })
                ->visible(function () {
                    return $this->record->status === 'draft' &&
                        $this->record->mahasiswa_id === auth()->user()->mahasiswa->id;
                });
        }

        // Aksi untuk admin/staf
        if ($user->hasAnyRole(['admin', 'staf'])) {
            $actions[] = Actions\Action::make('updateStatus')
                ->label('Update Status')
                ->icon('heroicon-o-check-circle')
                ->modalWidth('md')
                ->modalHeading('Update Status Pendaftaran')
                ->form([
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'verifikasi' => 'Menunggu Verifikasi',
                            'diterima' => 'Diterima',
                            'ditolak' => 'Ditolak',
                        ])
                        ->required()
                        ->default(fn() => $this->record->status),

                    Forms\Components\Textarea::make('note')
                        ->label('Catatan')
                        ->placeholder('Tambahkan catatan untuk mahasiswa (opsional)')
                        ->default(fn() => $this->record->note)
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'status' => $data['status'],
                        'note' => $data['note'],
                    ]);

                    $statusLabel = match ($data['status']) {
                        'verifikasi' => 'menunggu verifikasi',
                        'diterima' => 'diterima',
                        'ditolak' => 'ditolak',
                        default => $data['status'],
                    };

                    Notification::make()
                        ->title('Status Diperbarui')
                        ->body("Pendaftaran telah {$statusLabel}.")
                        ->success()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                })
                ->visible(fn() => $this->record->status !== 'draft');
        }

        return $actions;
    }

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\EditAction::make()
    //             ->visible(fn() => auth()->user()->hasRole('mahasiswa') && $this->record->status === 'draft'),

    //         Actions\Action::make('kirim')
    //             ->label('Kirim Pendaftaran')
    //             ->icon('heroicon-o-paper-airplane')
    //             ->requiresConfirmation()
    //             ->modalHeading('Konfirmasi Pendaftaran')
    //             ->modalDescription('Setelah dikirim anda tidak dapat mengedit pendaftaran ini lagi. Pastikan semua data dan berkas sudah benar.')
    //             ->modalSubmitActionLabel('Ya, Kirim Pendaftaran')
    //             ->modalCancelActionLabel('Batal')
    //             ->color('success')
    //             ->action(function () {
    //                 $record = $this->record;

    //                 $jumlahBerkas = $record->berkasPendaftar()->count();
    //                 if ($jumlahBerkas === 0) {
    //                     Notification::make()
    //                         ->title('Gagal Mengirim')
    //                         ->body('Anda belum mengupload berkas apapun. Silakan edit pendaftaran dan upload berkas terlebih dahulu.')
    //                         ->danger()
    //                         ->send();
    //                     return;
    //                 }

    //                 $record->update([
    //                     'status' => 'verifikasi',
    //                 ]);

    //                 Notification::make()
    //                     ->title('Pendaftaran Terkirim!')
    //                     ->body('Pendaftaran Anda telah dikirim untuk diverifikasi. Silakan tunggu hasil verifikasi dari admin.')
    //                     ->success()
    //                     ->send();

    //                 // Refresh halaman
    //                 $this->redirect($this->getResource()::getUrl('view', ['record' => $record]));
    //             })
    //             ->visible(function () {
    //                 $user = auth()->user();
    //                 $record = $this->record;

    //                 return $user->hasRole('mahasiswa')
    //                     && $record->status === 'draft'
    //                     && $record->mahasiswa_id === $user->mahasiswa->id;
    //             })
    //     ];
    // }
}
