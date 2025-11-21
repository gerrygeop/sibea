<?php

namespace App\Filament\Resources\PeriodeBeasiswaResource\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\PeriodeBeasiswaResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class ManagePeriodeBeasiswaPengelola extends ManageRelatedRecords
{
    protected static string $resource = PeriodeBeasiswaResource::class;

    protected static string $relationship = 'pengelola';
    protected static null|string $title = 'Pengelola Periode Beasiswa';

    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return 'Pengelola';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('nim')
                            ->label('Username')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
                            ->dehydrated(fn(?string $state): bool => filled($state))
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->minLength(8)
                            ->maxLength(255),

                        Forms\Components\Select::make('role_id')
                            ->relationship('role', 'name')
                            ->preload()
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make()
                    ->schema([
                        Components\TextEntry::make('name')
                            ->label('Nama'),
                        Components\TextEntry::make('nim')
                            ->label('Username'),
                        Components\TextEntry::make('role.name')
                            ->label('Role')
                            ->badge(),
                    ])
                    ->columns(2)
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nim')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama'),
                Tables\Columns\TextColumn::make('nim')
                    ->label('Username'),
                Tables\Columns\TextColumn::make('role.name')
                    ->label('Role')
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make()
                    ->modelLabel('Pengelola')
                    ->recordSelectSearchColumns(['nim', 'id'])
                    ->recordSelectOptionsQuery(
                        fn($query) => $query->where('role_id', [UserRole::PENGELOLA_ID])
                    )
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}
