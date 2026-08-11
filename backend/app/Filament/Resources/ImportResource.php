<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImportResource\Pages;
use App\Filament\Resources\ImportResource\RelationManagers\LogsRelationManager;
use App\Models\Import;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ImportResource extends Resource
{
    protected static ?string $model = Import::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static ?string $navigationLabel = 'Imports';

    protected static ?string $modelLabel = 'import';

    protected static ?string $pluralModelLabel = 'imports';

    protected static ?string $navigationGroup = 'Administration';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->disabled(),
                Forms\Components\TextInput::make('source')
                    ->disabled(),
                Forms\Components\TextInput::make('fichier')
                    ->disabled(),
                Forms\Components\TextInput::make('statut')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),
                Tables\Columns\TextColumn::make('source')
                    ->searchable(),
                Tables\Columns\TextColumn::make('statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'partial' => 'warning',
                        'running' => 'info',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('lignes_total')
                    ->label('Total'),
                Tables\Columns\TextColumn::make('lignes_inserees')
                    ->label('Insérées'),
                Tables\Columns\TextColumn::make('lignes_maj')
                    ->label('Maj'),
                Tables\Columns\TextColumn::make('lignes_erreur')
                    ->label('Erreurs'),
                Tables\Columns\TextColumn::make('started_at')
                    ->label('Démarré')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            LogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImports::route('/'),
            'view' => Pages\ViewImport::route('/{record}'),
        ];
    }
}
