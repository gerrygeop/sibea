<?php

namespace App\Filament\Resources\BeasiswaResource\RelationManagers;

use App\Models\Mahasiswa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MahasiswasRelationManager extends RelationManager
{
    protected static string $relationship = 'mahasiswas';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\TextInput::make('user_id')
                // ->required()
                // ->numeric(),
                Forms\Components\TextInput::make('nama')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                Forms\Components\TextInput::make('ttl')
                    ->required(),
                Forms\Components\TextInput::make('no_hp')
                    ->required(),
                Forms\Components\TextInput::make('prodi')
                    ->required(),
                Forms\Components\TextInput::make('fakultas')
                    ->required(),
                Forms\Components\TextInput::make('angkatan')
                    ->required(),
                Forms\Components\TextInput::make('sks')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('semester')
                    ->required(),
                Forms\Components\TextInput::make('ip')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('ipk')
                    ->required()
                    ->numeric(),

                Forms\Components\Select::make('status')
                    ->options([
                        'menunggu_verifikasi' => 'menunggu_verifikasi',
                        'lolos_verifikasi' => 'lolos_verifikasi',
                        'ditolak' => 'ditolak',
                        'diterima' => 'diterima',
                    ])
                    ->visible(fn() => auth()->user()->hasAnyRole(['admin', 'staf'])),

                Forms\Components\DatePicker::make('tanggal_penerimaan')
                    ->required()
                    ->visible(fn() => auth()->user()->hasAnyRole(['admin', 'staf'])),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama')
            ->columns([
                Tables\Columns\TextColumn::make('user.nim')
                    ->label('NIM')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ttl')
                    ->label('Tempat, Tanggal Lahir')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('no_hp')
                    ->searchable(),
                Tables\Columns\TextColumn::make('prodi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fakultas')
                    ->searchable(),
                Tables\Columns\TextColumn::make('angkatan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sks')
                    ->label('SKS')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pivot.status')
                    ->badge()
                    ->label('Status'),

                Tables\Columns\TextColumn::make('semester')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ip')
                    ->label('IP')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ipk')
                    ->label('IPK')
                    ->numeric()
                    ->sortable()
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
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect(),
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
