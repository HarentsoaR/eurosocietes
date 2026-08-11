<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HistoriqueResource\Pages;
use App\Models\Historique;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HistoriqueResource extends Resource
{
    protected static ?string $model = Historique::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Journal d’audit';

    protected static ?string $modelLabel = 'entrée de journal';

    protected static ?string $pluralModelLabel = 'journal d’audit';

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
                Forms\Components\Select::make('entity_type')
                    ->disabled()
                    ->dehydrated(),
                Forms\Components\TextInput::make('entity_id')
                    ->disabled(),
                Forms\Components\TextInput::make('action')
                    ->disabled(),
                Forms\Components\Textarea::make('avant')
                    ->disabled(),
                Forms\Components\Textarea::make('apres')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('entity_type')
                    ->label('Entité')
                    ->formatStateUsing(fn (string $state): string => class_basename($state)),
                Tables\Columns\TextColumn::make('entity_id')
                    ->label('ID'),
                Tables\Columns\TextColumn::make('action')
                    ->badge(),
                Tables\Columns\TextColumn::make('utilisateur.name')
                    ->label('Utilisateur'),
                Tables\Columns\TextColumn::make('import_id')
                    ->label('Import'),
                Tables\Columns\TextColumn::make('ip')
                    ->label('IP')
                    ->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->options([
                        'create' => 'Création',
                        'update' => 'Modification',
                        'delete' => 'Suppression',
                        'bloc_move' => 'Déplacement de bloc',
                    ]),
                Tables\Filters\SelectFilter::make('utilisateur')
                    ->relationship('utilisateur', 'name'),
            ])
            ->actions([])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHistoriques::route('/'),
        ];
    }
}
