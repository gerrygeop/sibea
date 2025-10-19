<?php

namespace App\Filament\Resources\PeriodeBeasiswaResource\RelationManagers;

use App\Filament\Resources\PendaftaranResource;
use App\Filament\Resources\PeriodeBeasiswaResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PendaftaransRelationManager extends RelationManager
{
    protected static string $relationship = 'pendaftarans';
    protected static null|string $title = 'Pendaftar';
    protected static null|string $label = 'Pendaftaran Beasiswa';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\TextInput::make('periode_beasiswa_id')
                //     ->required()
                //     ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('periode_beasiswa_id')
            ->columns([
                Tables\Columns\TextColumn::make('mahasiswa.nama')
                    ->label('Nama Mahasiswa')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('mahasiswa.user.nim')
                    ->label('NIM')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'secondary',
                        'verifikasi' => 'warning',
                        'diterima' => 'success',
                        'ditolak' => 'danger',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'verifikasi' => 'Menunggu Verifikasi',
                        'diterima' => 'Diterima',
                        'ditolak' => 'Ditolak',
                        default => ucfirst($state),
                    }),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make()
                //     ->label('Daftarkan Mahasiswa')
                //     ->url(fn($record) => PendaftaranResource::getUrl('create', ['record' => $record])),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn($record) => PendaftaranResource::getUrl('view', ['record' => $record])),
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
                // Tables\Actions\ForceDeleteAction::make(),
                // Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                //     Tables\Actions\ForceDeleteBulkAction::make(),
                //     Tables\Actions\RestoreBulkAction::make(),
                // ]),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]))
            ->defaultSort('created_at', 'desc');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'staff']);
    }
}
