<?php

namespace App\Filament\Resources;

use App\Filament\Exports\BeasiswaExporter;
use App\Filament\Resources\BeasiswaResource\Pages;
use App\Filament\Resources\BeasiswaResource\RelationManagers\MahasiswasRelationManager;
use App\Filament\Resources\BeasiswaResource\RelationManagers\PeriodeBeasiswasRelationManager;
use App\Models\Beasiswa;
use App\Models\PeriodeBeasiswa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BeasiswaResource extends Resource
{
    protected static ?string $model = Beasiswa::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('nama_beasiswa')
                            ->required(),

                        Forms\Components\Select::make('kategori_id')
                            ->relationship('kategori', 'nama_kategori')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('lembaga_penyelenggara')
                            ->required(),

                        Forms\Components\Textarea::make('deskripsi')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->components([
                Components\Section::make()
                    ->schema([
                        Components\TextEntry::make('nama_beasiswa'),
                        Components\TextEntry::make('kategori.nama_kategori'),
                        Components\TextEntry::make('lembaga_penyelenggara'),
                        // Components\TextEntry::make('besar_beasiswa')
                        //     ->numeric()
                        //     ->money('idr'),

                        Components\TextEntry::make('deskripsi')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpan(2),

                Components\Section::make()
                    ->schema([
                        Components\TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('-'),
                        Components\TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder('-'),
                        Components\TextEntry::make('deleted_at')
                            ->dateTime()
                            ->visible(fn(Beasiswa $record): bool => $record->trashed()),
                    ])
                    ->columnSpan(1),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_beasiswa')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('kategori.nama_kategori')
                    ->searchable(),

                Tables\Columns\TextColumn::make('lembaga_penyelenggara')
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
                Tables\Filters\SelectFilter::make('kategori')
                    ->relationship('kategori', 'nama_kategori'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->exporter(BeasiswaExporter::class),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
                Tables\Actions\ExportBulkAction::make()
                    ->exporter(BeasiswaExporter::class),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // MahasiswasRelationManager::class
            PeriodeBeasiswasRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBeasiswas::route('/'),
            'create' => Pages\CreateBeasiswa::route('/create'),
            'view' => Pages\ViewBeasiswa::route('/{record}'),
            'edit' => Pages\EditBeasiswa::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        // Jika user adalah admin atau staf, tampilkan semua beasiswa.
        if ($user->hasAnyRole(['admin', 'staf'])) {
            return parent::getEloquentQuery()->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
        }

        // Jika user adalah mahasiswa...
        if ($user->hasRole('mahasiswa')) {
            $mahasiswaId = $user->mahasiswa?->id;

            if ($mahasiswaId) {
                return parent::getEloquentQuery()
                    ->whereHas('mahasiswas', function (Builder $query) use ($mahasiswaId) {
                        $query->where('mahasiswas.id', $mahasiswaId);
                    })
                    ->with(['mahasiswas' => function ($query) use ($mahasiswaId) {
                        $query->where('mahasiswas.id', $mahasiswaId)
                            ->select('mahasiswas.id')
                            ->withPivot('status');
                    }])->withoutGlobalScopes([
                        SoftDeletingScope::class,
                    ]);
            }

            return parent::getEloquentQuery()->whereRaw('1 = 0')->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
        }

        // Jika tidak memiliki role di atas, jangan tampilkan apa-apa.
        return parent::getEloquentQuery()->whereRaw('1 = 0')->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
    }
}
