<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PasseportResource\Pages;
use App\Models\Passeport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PasseportResource extends Resource
{
    protected static ?string $model = Passeport::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationLabel = 'Passeports';

    protected static ?string $modelLabel = 'passeport';

    protected static ?string $pluralModelLabel = 'passeports';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Société')
                    ->schema([
                        Forms\Components\Select::make('entreprise_id')
                            ->relationship('entreprise', 'denomination')
                            ->disabled()
                            ->required(),
                    ]),
                Forms\Components\Section::make('Score & contenu')
                    ->schema([
                        Forms\Components\TextInput::make('score_confidence')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                        Forms\Components\TagsInput::make('badges')
                            ->placeholder('Ajouter un badge')
                            ->columnSpan(2),
                        Forms\Components\Select::make('statut')
                            ->options([
                                'non_soumis' => 'Non soumis',
                                'en_cours' => 'En cours de constitution',
                                'soumis' => 'Soumis pour validation',
                                'valide' => 'Validé',
                                'refuse' => 'Refusé',
                            ]),
                        Forms\Components\Textarea::make('commentaire'),
                    ]),
                Forms\Components\Section::make('Validation (lecture seule)')
                    ->schema([
                        Forms\Components\Toggle::make('is_validated')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('validated_at')
                            ->disabled(),
                        Forms\Components\Select::make('validateur_id')
                            ->relationship('validateur', 'name')
                            ->disabled(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('entreprise.denomination')
                    ->label('Entreprise')
                    ->searchable(),
                Tables\Columns\TextColumn::make('score_confidence')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 70 => 'success',
                        $state >= 40 => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('badges')
                    ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : ''),
                Tables\Columns\TextColumn::make('statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'valide' => 'success',
                        'refuse' => 'danger',
                        'soumis' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_validated')
                    ->boolean(),
                Tables\Columns\TextColumn::make('validated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->options([
                        'non_soumis' => 'Non soumis',
                        'en_cours' => 'En cours',
                        'soumis' => 'Soumis',
                        'valide' => 'Validé',
                        'refuse' => 'Refusé',
                    ]),
                Tables\Filters\TernaryFilter::make('is_validated'),
            ])
            ->actions([
                Tables\Actions\Action::make('valider')
                    ->label('Valider')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Passeport $record): bool => ! $record->getAttribute('is_validated'))
                    ->requiresConfirmation()
                    ->action(function (Passeport $record): void {
                        $record->forceFill([
                            'is_validated' => true,
                            'validated_at' => now(),
                            'validateur_id' => auth('web')->id(),
                            'statut' => 'valide',
                        ])->save();
                        Notification::make()->title('Passeport validé')->success()->send();
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPasseports::route('/'),
            'edit' => Pages\EditPasseport::route('/{record}/edit'),
        ];
    }
}