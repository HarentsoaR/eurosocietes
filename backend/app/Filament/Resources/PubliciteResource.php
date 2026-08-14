<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PubliciteResource\Pages;
use App\Models\Publicite;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PubliciteResource extends Resource
{
    protected static ?string $model = Publicite::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'Publicités';

    protected static ?string $navigationGroup = 'Monétisation';

    protected static ?string $modelLabel = 'publicité';

    protected static ?string $pluralModelLabel = 'publicités';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Contenu')
                    ->schema([
                        Forms\Components\Select::make('entreprise_id')
                            ->relationship('entreprise', 'denomination')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('titre')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description'),
                        Forms\Components\TextInput::make('url_cible')
                            ->url()
                            ->label('URL de destination'),
                        Forms\Components\FileUpload::make('visuels')
                            ->label('Visuel')
                            ->image()
                            ->disk('public')
                            ->directory('publicites')
                            ->maxSize(5120)
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Diffusion')
                    ->schema([
                        Forms\Components\Select::make('emplacement')
                            ->options([
                                'fiche_entreprise' => 'Fiche entreprise',
                                'resultats_recherche' => 'Résultats de recherche',
                                'accueil' => 'Page d’accueil',
                            ])
                            ->required(),
                        Forms\Components\Select::make('statut')
                            ->options([
                                'brouillon' => 'Brouillon',
                                'en_revision' => 'En révision',
                                'publie' => 'Publié',
                                'archive' => 'Archivé',
                            ])
                            ->required(),
                        Forms\Components\DatePicker::make('date_debut'),
                        Forms\Components\DatePicker::make('date_fin'),
                        Forms\Components\TextInput::make('budget')
                            ->numeric()
                            ->prefix('€'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Statistiques (lecture seule)')
                    ->schema([
                        Forms\Components\TextInput::make('impressions')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('clics')
                            ->numeric()
                            ->disabled(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('entreprise.denomination')
                    ->label('Entreprise')
                    ->searchable(),
                Tables\Columns\TextColumn::make('titre')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('emplacement')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'fiche_entreprise' => 'Fiche entreprise',
                        'resultats_recherche' => 'Résultats de recherche',
                        default => 'Accueil',
                    }),
                Tables\Columns\TextColumn::make('statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'publie' => 'success',
                        'en_revision' => 'warning',
                        'archive' => 'gray',
                        default => 'info',
                    }),
                Tables\Columns\TextColumn::make('date_debut')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_fin')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->options([
                        'brouillon' => 'Brouillon',
                        'en_revision' => 'En révision',
                        'publie' => 'Publié',
                        'archive' => 'Archivé',
                    ]),
                Tables\Filters\SelectFilter::make('emplacement')
                    ->options([
                        'fiche_entreprise' => 'Fiche entreprise',
                        'resultats_recherche' => 'Résultats de recherche',
                        'accueil' => 'Accueil',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('publier')
                    ->label('Publier')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Publicite $record): bool => $record->getAttribute('statut') !== 'publie')
                    ->requiresConfirmation()
                    ->action(function (Publicite $record): void {
                        $record->forceFill(['statut' => 'publie'])->save();
                        Notification::make()->title('Publicité publiée')->success()->send();
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPublicites::route('/'),
            'create' => Pages\CreatePublicite::route('/create'),
            'edit' => Pages\EditPublicite::route('/{record}/edit'),
        ];
    }
}
