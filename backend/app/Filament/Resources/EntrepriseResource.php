<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EntrepriseResource\Pages;
use App\Filament\Resources\EntrepriseResource\RelationManagers\SectionsRelationManager;
use App\Models\Entreprise;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EntrepriseResource extends Resource
{
    protected static ?string $model = Entreprise::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Fiches entreprises';

    protected static ?string $modelLabel = 'fiche entreprise';

    protected static ?string $pluralModelLabel = 'fiches entreprises';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identité')
                    ->schema([
                        Forms\Components\TextInput::make('siren')
                            ->label('SIREN (lecture seule)')
                            ->disabled()
                            ->maxLength(9),
                        Forms\Components\TextInput::make('denomination')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nom')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('prenoms')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('enseigne')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug (non modifiable)')
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Activité & géographie')
                    ->schema([
                        Forms\Components\Select::make('activite_naf_id')
                            ->relationship('activiteNaf', 'libelle')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('ville_id')
                            ->relationship('ville', 'libelle')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('adresse_complete'),
                        Forms\Components\Select::make('tranche_effectifs')
                            ->options([
                                '00' => '0 salarié',
                                '01' => '1 à 5 salariés',
                                '02' => '6 à 9 salariés',
                                '03' => '10 à 19 salariés',
                                '11' => '20 à 49 salariés',
                                '12' => '50 à 99 salariés',
                                '21' => '100 à 199 salariés',
                                '22' => '200 à 249 salariés',
                                '31' => '250 à 499 salariés',
                                '41' => '500 à 999 salariés',
                                '51' => '1 000 à 1 999 salariés',
                                '52' => '2 000 à 4 999 salariés',
                                '53' => '5 000 à 9 999 salariés',
                                '54' => '10 000+ salariés',
                                'NN' => 'Non renseigné',
                            ]),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Publication')
                    ->schema([
                        Forms\Components\Select::make('etat_administratif')
                            ->options([
                                'A' => 'Active',
                                'C' => 'Cessée',
                                'F' => 'Fusionnée',
                            ]),
                        Forms\Components\Toggle::make('visible')
                            ->label('Fiche visible sur le site'),
                        Forms\Components\Toggle::make('allow_public_contacts')
                            ->label('Permettre la prise de contact publique'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('siren')
                    ->searchable(),
                Tables\Columns\TextColumn::make('denomination')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('activiteNaf.code')
                    ->label('NAF')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ville.libelle')
                    ->label('Ville')
                    ->sortable(),
                Tables\Columns\TextColumn::make('etat_administratif')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'A' => 'Active',
                        'C' => 'Cessée',
                        'F' => 'Fusionnée',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'A' => 'success',
                        'C' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\ToggleColumn::make('visible'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('etat_administratif')
                    ->options([
                        'A' => 'Active',
                        'C' => 'Cessée',
                        'F' => 'Fusionnée',
                    ]),
                Tables\Filters\TernaryFilter::make('visible'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('denomination');
    }

    public static function getRelations(): array
    {
        return [
            SectionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEntreprises::route('/'),
            'view' => Pages\ViewEntreprise::route('/{record}'),
            'edit' => Pages\EditEntreprise::route('/{record}/edit'),
        ];
    }
}
