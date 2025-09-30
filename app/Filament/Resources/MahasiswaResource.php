<?php

namespace App\Filament\Resources;

use App\Filament\Exports\MahasiswaExporter;
use App\Filament\Resources\MahasiswaResource\Pages;
use App\Filament\Resources\MahasiswaResource\RelationManagers\BeasiswasRelationManager;
use App\Models\Mahasiswa;
use App\Models\User;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Illuminate\Support\Facades\Hash;

class MahasiswaResource extends Resource
{
    protected static ?string $model = Mahasiswa::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Section::make('Akun Mahasiswa')
                    ->schema([
                        Components\TextInput::make('nim')
                            ->label('NIM')
                            ->numeric()
                            ->unique(
                                table: User::class,
                                column: 'nim',
                                ignorable: fn(?Mahasiswa $record): ?User => $record?->user,
                            )
                            ->required(),

                        Components\TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
                            ->dehydrated(fn(?string $state): bool => filled($state))
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->minLength(8)
                            ->maxLength(255)
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Components\Section::make('Data Diri Mahasiswa')
                    ->schema([
                        Components\TextInput::make('nama')
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('no_hp')
                            ->tel()
                            ->required()
                            ->regex('/^(\+62|62|0)8[0-9]{8,12}$/')
                            ->validationMessages([
                                'regex' => 'Format nomor HP tidak valid. Contoh: 081234567890',
                            ]),

                        Components\TextInput::make('prodi')
                            ->required(),

                        Components\TextInput::make('fakultas')
                            ->required(),

                        Components\TextInput::make('angkatan')
                            ->numeric()
                            ->required(),

                        Components\TextInput::make('ip')
                            ->label('IP')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(4),

                        Components\TextInput::make('ipk')
                            ->label('IPK')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(4),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->components([
                Section::make()
                    ->schema([
                        TextEntry::make('user.nim')
                            ->label('NIM'),

                        TextEntry::make('nama'),
                        TextEntry::make('email'),
                        TextEntry::make('ttl')
                            ->label('Tempat, Tanggal Lahir'),
                        TextEntry::make('no_hp'),
                        TextEntry::make('prodi'),
                        TextEntry::make('fakultas'),
                        TextEntry::make('angkatan'),
                        TextEntry::make('sks')
                            ->numeric(),
                        TextEntry::make('semester'),
                        TextEntry::make('ip')
                            ->label('IP')
                            ->badge()
                            ->color('info'),
                        TextEntry::make('ipk')
                            ->label('IPK')
                            ->badge(),
                    ])
                    ->columns(2)
                    ->columnSpan(2),

                Section::make()
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('deleted_at')
                            ->dateTime()
                            ->visible(fn(Mahasiswa $record): bool => $record->trashed()),
                    ])
                    ->columnSpan(1),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.nim')
                    ->label('NIM')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('nama')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('no_hp')
                    ->searchable(),

                Tables\Columns\TextColumn::make('prodi')
                    ->searchable(),

                Tables\Columns\TextColumn::make('fakultas')
                    ->searchable(),

                Tables\Columns\TextColumn::make('angkatan')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('ip')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('ipk')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('angkatan')->options(
                    Mahasiswa::query()->distinct()->pluck('angkatan', 'angkatan')->toArray()
                )->label('Angkatan'),
                Tables\Filters\SelectFilter::make('fakultas')->options(
                    Mahasiswa::query()->distinct()->pluck('fakultas', 'fakultas')->toArray()
                )->label('Fakultas'),
                Tables\Filters\SelectFilter::make('prodi')->options(
                    Mahasiswa::query()->distinct()->pluck('prodi', 'prodi')->toArray()
                )->label('Prodi'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->exporter(MahasiswaExporter::class),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
                Tables\Actions\ExportBulkAction::make()
                    ->exporter(MahasiswaExporter::class),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            BeasiswasRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMahasiswas::route('/'),
            'create' => Pages\CreateMahasiswa::route('/create'),
            'view' => Pages\ViewMahasiswa::route('/{record}'),
            'edit' => Pages\EditMahasiswa::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
