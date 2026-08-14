<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContenuIaResource\Pages;
use App\Jobs\RegenerateContenuIa;
use App\Models\ContenuIa;
use App\Models\Entreprise;
use App\Models\Ville;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Queue;

class ContenuIaResource extends Resource
{
    protected static ?string $model = ContenuIa::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationLabel = 'Contenus IA';

    protected static ?string $navigationGroup = 'Contenus';

    protected static ?string $modelLabel = 'contenu IA';

    protected static ?string $pluralModelLabel = 'contenus IA';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Cible')
                    ->schema([
                        Forms\Components\Select::make('entity_type')
                            ->label('Type de cible')
                            ->disabledOn('edit')
                            ->options([
                                Entreprise::class => 'Entreprise',
                                Ville::class => 'Ville',
                            ])
                            ->default(Entreprise::class)
                            ->reactive()
                            ->required(),
                        Forms\Components\Select::make('entity_id')
                            ->label('Cible')
                            ->disabledOn('edit')
                            ->options(fn (Get $get): array => match ($get('entity_type')) {
                                Ville::class => Ville::query()->orderBy('libelle')->limit(500)->pluck('libelle', 'id')->all(),
                                default => Entreprise::query()->orderBy('denomination')->limit(500)->pluck('denomination', 'id')->all(),
                            })
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('type_contenu')
                            ->label('Type de contenu')
                            ->required()
                            ->maxLength(50),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Génération')
                    ->schema([
                        Forms\Components\Textarea::make('contenu')
                            ->rows(10)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('statut')
                            ->options([
                                'pending' => 'En attente',
                                'generating' => 'En cours de génération',
                                'done' => 'Généré',
                                'failed' => 'Échec',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('modele')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('prompt_version')
                            ->maxLength(20),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('entity_type')
                    ->label('Cible')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Ville::class => 'Ville',
                        default => 'Entreprise',
                    }),
                Tables\Columns\TextColumn::make('entity_id')
                    ->label('ID cible'),
                Tables\Columns\TextColumn::make('type_contenu')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'En attente',
                        'generating' => 'En cours',
                        'done' => 'Généré',
                        'failed' => 'Échec',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'done' => 'success',
                        'generating' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('generated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('entity_type')
                    ->label('Cible')
                    ->options([
                        Entreprise::class => 'Entreprise',
                        Ville::class => 'Ville',
                    ]),
                Tables\Filters\SelectFilter::make('statut')
                    ->options([
                        'pending' => 'En attente',
                        'generating' => 'En cours',
                        'done' => 'Généré',
                        'failed' => 'Échec',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('regenerer')
                    ->label('Régénérer')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (ContenuIa $record): void {
                        Queue::push(new RegenerateContenuIa($record));
                        Notification::make()->title('Régénération planifiée')->success()->send();
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContenusIa::route('/'),
            'edit' => Pages\EditContenuIa::route('/{record}/edit'),
        ];
    }
}
