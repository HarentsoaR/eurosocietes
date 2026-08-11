<?php

namespace App\Filament\Resources\ImportResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LogsRelationManager extends RelationManager
{
    protected static string $relationship = 'logs';

    protected static ?string $title = 'Logs d’import';

    protected static ?string $modelLabel = 'log';

    protected static ?string $pluralModelLabel = 'logs';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('niveau')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'error' => 'danger',
                        'warning' => 'warning',
                        default => 'info',
                    }),
                Tables\Columns\TextColumn::make('siren')
                    ->label('SIREN'),
                Tables\Columns\TextColumn::make('siret')
                    ->label('SIRET'),
                Tables\Columns\TextColumn::make('ligne')
                    ->label('Ligne'),
                Tables\Columns\TextColumn::make('message')
                    ->limit(80)
                    ->wrap(),
            ])
            ->actions([])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }
}
