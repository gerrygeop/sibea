<?php

namespace App\Filament\Resources\BeasiswaResource\RelationManagers;

use App\Filament\Resources\PeriodeBeasiswaResource;
use App\Models\PeriodeBeasiswa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PeriodeBeasiswasRelationManager extends RelationManager
{
    protected static string $relationship = 'periodeBeasiswas';
    protected static null|string $title = 'Periode Beasiswa';
    protected static null|string $label = 'Periode Beasiswa';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_periode')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('besar_beasiswa')
                    ->prefix('Rp')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0),

                Forms\Components\DatePicker::make('tanggal_mulai_daftar')
                    ->required(),

                Forms\Components\DatePicker::make('tanggal_akhir_daftar')
                    ->required(),

                Forms\Components\Toggle::make('is_aktif')
                    ->label('Aktif')
                    ->default(false),

                Forms\Components\Section::make('Persyaratan Pre-Check')
                    ->schema([
                        Forms\Components\Repeater::make('persyaratans_json')
                            ->label('Persyaratan Pre-Check')
                            ->schema([
                                Forms\Components\Select::make('jenis')
                                    ->options([
                                        'IPK' => 'Indeks prestasi kumulatif (IPK)',
                                        'Semester' => 'Semester',
                                        'SKS' => 'Satuan Kredit Semester (SKS)',
                                    ])
                                    ->required(),

                                Forms\Components\TextInput::make('nilai')
                                    ->numeric()
                                    ->minValue(0)
                                    ->nullable(),

                                Forms\Components\Select::make('keterangan')
                                    ->options([
                                        'Minimal' => 'Minimal',
                                        'Maksimal' => 'Maksimal',
                                    ])
                                    ->required(),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Berkas yang Wajib Diupload')
                    ->schema([
                        Forms\Components\Select::make('berkasWajibs')
                            ->relationship('berkasWajibs', 'nama_berkas')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('nama_berkas')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('deskripsi')
                                    ->placeholder('Terbaru/Berlaku/Semester 1-5 dll')
                                    ->nullable(),
                            ])
                            ->hiddenLabel()
                            ->helperText('Pilih berkas yang wajib diupload oleh pendaftar beasiswa.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->components([
                Components\Section::make()
                    ->schema([
                        Components\TextEntry::make('nama_periode'),
                        Components\TextEntry::make('besar_beasiswa')
                            ->money('idr'),
                        Components\TextEntry::make('tanggal_mulai_daftar')
                            ->date(),
                        Components\TextEntry::make('tanggal_akhir_daftar')
                            ->date(),
                        Components\IconEntry::make('is_aktif')
                            ->label('Aktif')
                            ->boolean(),
                    ])
                    ->columns(2),

                Components\Section::make('Berkas Wajib Upload')
                    ->schema([
                        Components\RepeatableEntry::make('berkasWajibs')
                            ->label('')
                            ->schema([
                                Components\TextEntry::make('nama_berkas')
                                    ->label(''),
                                Components\TextEntry::make('deskripsi')
                                    ->placeholder('-')
                                    ->label(''),
                            ])
                            ->contained(false)
                            ->columns(2),
                    ]),

                Components\Section::make('Persyaratan Pre-Check')
                    ->schema([
                        Components\RepeatableEntry::make('persyaratans_json')
                            ->label('')
                            ->schema([
                                Components\TextEntry::make('jenis')
                                    ->label(''),
                                Components\TextEntry::make('nilai')
                                    ->label(''),
                                Components\TextEntry::make('keterangan')
                                    ->label(''),
                            ])
                            ->columns(3),
                    ]),

                Components\Section::make('Informasi tambahan')
                    ->schema([
                        Components\TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('-'),
                        Components\TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder('-'),
                        Components\TextEntry::make('deleted_at')
                            ->dateTime()
                            ->visible(fn(PeriodeBeasiswa $record): bool => $record->trashed()),
                    ])
                    ->collapsed()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_periode')
            ->columns([
                Tables\Columns\TextColumn::make('nama_periode')
                    ->searchable(),

                Tables\Columns\TextColumn::make('besar_beasiswa')
                    ->money('idr')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_mulai_daftar')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_akhir_daftar')
                    ->date()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_aktif')
                    ->label('Aktif')
                    ->boolean(),

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
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                // ->url(fn($record) => PeriodeBeasiswaResource::getUrl('edit', ['record' => $record])),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
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
}
