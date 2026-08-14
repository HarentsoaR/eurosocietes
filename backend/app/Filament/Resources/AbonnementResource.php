<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AbonnementResource\Pages;
use App\Models\Abonnement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AbonnementResource extends Resource
{
    protected static ?string $model = Abonnement::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Abonnements';

    protected static ?string $navigationGroup = 'Monétisation';

    protected static ?string $modelLabel = 'abonnement';

    protected static ?string $pluralModelLabel = 'abonnements';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Contrat')
                    ->schema([
                        Forms\Components\Select::make('entreprise_id')
                            ->relationship('entreprise', 'denomination')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('plan')
                            ->options([
                                'gratuit' => 'Gratuit',
                                'essentiel' => 'Essentiel',
                                'premium' => 'Premium',
                                'entreprise' => 'Entreprise',
                            ])
                            ->required(),
                        Forms\Components\Select::make('statut')
                            ->options([
                                'actif' => 'Actif',
                                'suspendu' => 'Suspendu',
                                'expire' => 'Expiré',
                                'annule' => 'Annulé',
                            ])
                            ->required(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Période')
                    ->schema([
                        Forms\Components\DatePicker::make('date_debut'),
                        Forms\Components\DatePicker::make('date_fin'),
                        Forms\Components\Toggle::make('renouvellement_auto')
                            ->label('Renouvellement automatique'),
                        Forms\Components\TextInput::make('stripe_id')
                            ->label('Référence Stripe (lecture seule)')
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
                Tables\Columns\TextColumn::make('plan')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color('gray'),
                Tables\Columns\TextColumn::make('statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'actif' => 'success',
                        'suspendu' => 'warning',
                        'expire' => 'gray',
                        default => 'danger',
                    }),
                Tables\Columns\IconColumn::make('renouvellement_auto')
                    ->boolean()
                    ->label('Auto'),
                Tables\Columns\TextColumn::make('date_fin')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('plan')
                    ->options([
                        'gratuit' => 'Gratuit',
                        'essentiel' => 'Essentiel',
                        'premium' => 'Premium',
                        'entreprise' => 'Entreprise',
                    ]),
                Tables\Filters\SelectFilter::make('statut')
                    ->options([
                        'actif' => 'Actif',
                        'suspendu' => 'Suspendu',
                        'expire' => 'Expiré',
                        'annule' => 'Annulé',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAbonnements::route('/'),
            'edit' => Pages\EditAbonnement::route('/{record}/edit'),
        ];
    }
}
