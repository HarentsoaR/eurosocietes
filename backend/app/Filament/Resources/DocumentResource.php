<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Models\Document;
use App\Models\Entreprise;
use App\Models\Ville;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Documents';

    protected static ?string $modelLabel = 'document';

    protected static ?string $pluralModelLabel = 'documents';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Cible')
                    ->schema([
                        Forms\Components\Select::make('entity_type')
                            ->label('Type de cible')
                            ->options([
                                Entreprise::class => 'Entreprise',
                                Ville::class => 'Ville',
                            ])
                            ->default(Entreprise::class)
                            ->reactive()
                            ->required(),
                        Forms\Components\Select::make('entity_id')
                            ->label('Cible')
                            ->options(fn (Get $get): array => match ($get('entity_type')) {
                                Ville::class => Ville::query()->orderBy('libelle')->limit(500)->pluck('libelle', 'id')->all(),
                                default => Entreprise::query()->orderBy('denomination')->limit(500)->pluck('denomination', 'id')->all(),
                            })
                            ->searchable()
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Fichier')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Type de document')
                            ->options([
                                'statuts' => 'Statuts',
                                'kbis' => 'Extrait Kbis',
                                'rna' => 'Inscription RNA',
                                'bilan' => 'Bilan financier',
                                'piece_identite' => "Pièce d'identité",
                                'autre' => 'Autre',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('titre')
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('fichiers')
                            ->label('Fichier')
                            ->disk('public')
                            ->directory('documents')
                            ->maxSize(10240)
                            ->required(),
                        Forms\Components\Select::make('statut_validation')
                            ->options([
                                'en_attente' => 'En attente',
                                'valide' => 'Validé',
                                'rejete' => 'Rejeté',
                            ])
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('titre')
                    ->searchable()
                    ->default('-'),
                Tables\Columns\TextColumn::make('type')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'statuts' => 'Statuts',
                        'kbis' => 'Kbis',
                        'rna' => 'RNA',
                        'bilan' => 'Bilan',
                        'piece_identite' => 'Pièce d’identité',
                        default => 'Autre',
                    }),
                Tables\Columns\TextColumn::make('entity_type')
                    ->label('Cible')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Ville::class => 'Ville',
                        default => 'Entreprise',
                    }),
                Tables\Columns\TextColumn::make('entity_id')
                    ->label('ID cible'),
                Tables\Columns\TextColumn::make('fichier')
                    ->label('Fichier')
                    ->state(fn (Document $record): string => $record->getFirstMediaUrl('fichiers') ?: '—'),
                Tables\Columns\TextColumn::make('statut_validation')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'valide' => 'Validé',
                        'rejete' => 'Rejeté',
                        default => 'En attente',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'valide' => 'success',
                        'rejete' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('entity_type')
                    ->label('Cible')
                    ->options([
                        Entreprise::class => 'Entreprise',
                        Ville::class => 'Ville',
                    ]),
                Tables\Filters\SelectFilter::make('statut_validation')
                    ->options([
                        'en_attente' => 'En attente',
                        'valide' => 'Validé',
                        'rejete' => 'Rejeté',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }
}
