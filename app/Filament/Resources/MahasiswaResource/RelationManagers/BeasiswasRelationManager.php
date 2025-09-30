<?php

namespace App\Filament\Resources\MahasiswaResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components;

class BeasiswasRelationManager extends RelationManager
{
    protected static string $relationship = 'beasiswas';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Section::make()
                    ->schema([
                        Components\TextInput::make('nama_beasiswa')
                            ->required(),

                        Components\Select::make('kategori_id')
                            ->relationship('kategori', 'nama_kategori')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->required(),

                        Components\TextInput::make('lembaga_penyelenggara')
                            ->required(),

                        Components\TextInput::make('besar_beasiswa')
                            ->required()
                            ->numeric(),

                        Components\TextInput::make('periode')
                            ->required(),

                        Components\DatePicker::make('tanggal_penerimaan')
                            ->required(),

                        Components\Select::make('status')
                            ->options([
                                'menunggu_verifikasi' => 'menunggu_verifikasi',
                                'lolos_verifikasi' => 'lolos_verifikasi',
                                'ditolak' => 'ditolak',
                                'diterima' => 'diterima',
                            ])
                            ->visible(fn() => auth()->user()->hasAnyRole(['admin', 'staf'])),

                        Components\Textarea::make('deskripsi')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_beasiswa')
            ->columns([
                Tables\Columns\TextColumn::make('nama_beasiswa')
                    ->searchable(),

                Tables\Columns\TextColumn::make('kategori.nama_kategori')
                    ->searchable(),

                Tables\Columns\TextColumn::make('lembaga_penyelenggara')
                    ->searchable(),

                Tables\Columns\TextColumn::make('besar_beasiswa')
                    ->numeric()
                    ->money('idr')
                    ->sortable(),

                Tables\Columns\TextColumn::make('periode')
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->searchable(),

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
                Tables\Filters\SelectFilter::make('kategori_id')
                    ->label('Kategori Beasiswa')
                    ->relationship('kategori', 'nama_kategori'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->action(function (array $data, Tables\Actions\AttachAction $action) {
                        // Ambil record mahasiswa saat ini (owner)
                        $mahasiswa = $this->getOwnerRecord();

                        // Lakukan attach dengan menyertakan data pivot
                        $mahasiswa->beasiswas()->attach($data['recordId'], [
                            'tanggal_penerimaan' => now(),
                        ]);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
