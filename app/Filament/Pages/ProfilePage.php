<?php

namespace App\Filament\Pages;

use App\Services\ApiService;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;

class ProfilePage extends Page implements HasForms
{
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static string $view = 'filament.pages.profile-page';
    protected static ?string $title = 'Profile';
    protected static ?string $navigationLabel = 'Profile';
    protected static ?int $navigationSort = 99;

    public ?array $data = [];

    public function mount(): void
    {
        $user = auth()->user();

        if ($user->hasRole('mahasiswa')) {
            $mahasiswa = $user->mahasiswa;
            $this->form->fill([
                'nim' => $user->nim,
                'nama' => $mahasiswa->nama ?? '-',
                'email' => $mahasiswa->email ?? '-',
                'tempat_lahir' => $mahasiswa->tempat_lahir ?? '-',
                'tanggal_lahir' => $mahasiswa->tanggal_lahir ?? '-',
                'jenis_kelamin' => $mahasiswa->jenis_kelamin ?? '-',
                'no_hp' => $mahasiswa->no_hp ?? '-',
                'prodi' => $mahasiswa->prodi ?? '-',
                'fakultas' => $mahasiswa->fakultas ?? '-',
                'angkatan' => $mahasiswa->angkatan ?? '-',
                'semester' => $mahasiswa->semester ?? '-',
                'sks' => $mahasiswa->sks ?? '-',
                'ip' => $mahasiswa->ip ?? '-',
                'ipk' => $mahasiswa->ipk ?? '-',
                'status_mahasiswa' => $mahasiswa->status_mahasiswa ?? '-',
            ]);
        } else {
            $this->form->fill([
                'name' => $user->name,
                'nim' => $user->nim,
            ]);
        }
    }

    public function form(Form $form): Form
    {
        $user = auth()->user();

        if ($user->hasRole('mahasiswa')) {
            return $form
                ->schema([
                    Section::make('Data akun')
                        ->schema([
                            TextInput::make('nim')
                                ->label('NIM')
                                ->disabled()
                        ])
                        ->columns(1),

                    Section::make('Biodata Mahasiswa')
                        ->description('Data ini disinkronkan dari Portal Mahasiswa')
                        ->schema([
                            TextInput::make('nama')
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('email')
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('tempat_lahir')
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('tanggal_lahir')
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('jenis_kelamin')
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('no_hp')
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('prodi')
                                ->label('Program Studi')
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('fakultas')
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('angkatan')
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('semester')
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('sks')
                                ->label('SKS')
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('ip')
                                ->label('IP')
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('ipk')
                                ->label('IPK')
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('status_mahasiswa')
                                ->disabled()
                                ->dehydrated(),
                        ])
                        ->columns(2),
                ])
                ->statePath('data');
        }

        return $form
            ->schema([
                Section::make('Data akun')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required(),
                        TextInput::make('nim')
                            ->label('NIM / Username')
                            ->required(),
                        TextInput::make('password')
                            ->label('Password Baru')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn($state) => $state ? Hash::make($state) : null)
                            ->dehydrated(fn($state) => filled($state))
                            ->nullable(),
                    ])
                    ->columns(2)
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        $user = auth()->user();

        if ($user->hasRole('mahasiswa')) {
            return [
                Action::make('sync')
                    ->label('Sinkronkan Data')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Sinkronkan Data dari Portal Mahasiswa')
                    ->modalDescription('Data anda akan diperbarui dengan data terbaru dari Portal Mahasiswa.')
                    ->modalSubmitActionLabel('Ya, Sinkronkan')
                    ->action(function () use ($user) {
                        $apiService = app(ApiService::class);

                        try {
                            $biodata = $apiService->getBiodata($user->nim);

                            if (!$biodata) {
                                Notification::make()
                                    ->title('Gagal')
                                    ->body('Tidak dapat mengambil data dari Portal Mahasiswa')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $user->mahasiswa->update([
                                'nama' => $biodata['nama'],
                                'email' => $biodata['email'],
                                'tempat_lahir' => $biodata['tempat_lahir'],
                                'tanggal_lahir' => $biodata['tanggal_lahir'],
                                'no_hp' => $biodata['no_hp'],
                                'prodi' => $biodata['program_studi'],
                                'fakultas' => $biodata['fakultas'],
                                'angkatan' => $biodata['angkatan'],
                                'semester' => $biodata['semester'] ?? 0,
                                'sks' => $biodata['sks'] ?? 0,
                                'ip' => $biodata['ip'] ?? 0,
                                'ipk' => $biodata['ipk'] ?? 0,
                                'status_mahasiswa' => $biodata['status_mahasiswa'],
                            ]);

                            Notification::make()
                                ->title('Berhasil')
                                ->body('Data anda telah diperbarui dari Portal Mahasiswa.')
                                ->success()
                                ->send();

                            $this->mount();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error')
                                ->body('Terjadi kesalahan saat sinkronisasi data.')
                                ->danger()
                                ->send();
                        }
                    }),
            ];
        }

        return [
            Action::make('save')
                ->label('Simpan')
                ->action(function () use ($user) {
                    $data = $this->form->getState();

                    $updateData = [
                        'name' => $data['name'],
                        'nim' => $data['nim'],
                    ];

                    if (!empty($data['password'])) {
                        $updateData['password'] = $data['password'];
                    }

                    $user->update($updateData);

                    Notification::make()
                        ->title('Berhasil')
                        ->body('Data akun Anda telah diperbarui.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
