<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FaqResource\Pages;
use App\Models\Entreprise;
use App\Models\Faq;
use App\Models\Ville;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationLabel = 'Foire aux questions';

    protected static ?string $modelLabel = 'question';

    protected static ?string $pluralModelLabel = 'questions';

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
                                Ville::class => Ville::query()
                                    ->orderBy('libelle')
                                    ->limit(500)
                                    ->pluck('libelle', 'id')
                                    ->all(),
                                default => Entreprise::query()
                                    ->orderBy('denomination')
                                    ->limit(500)
                                    ->pluck('denomination', 'id')
                                    ->all(),
                            })
                            ->searchable()
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Contenu')
                    ->schema([
                        Forms\Components\Textarea::make('question')
                            ->required()
                            ->rows(3),
                        Forms\Components\Textarea::make('reponse')
                            ->required()
                            ->rows(5),
                        Forms\Components\TextInput::make('ordre')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Toggle::make('visible')
                            ->label('Visible')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('question')
                    ->searchable()
                    ->limit(60)
                    ->wrap(),
                Tables\Columns\TextColumn::make('entity_type')
                    ->label('Cible')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Ville::class => 'Ville',
                        default => 'Entreprise',
                    }),
                Tables\Columns\TextColumn::make('entity_id')
                    ->label('ID cible'),
                Tables\Columns\TextColumn::make('ordre')
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('visible'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('entity_type')
                    ->label('Cible')
                    ->options([
                        Entreprise::class => 'Entreprise',
                        Ville::class => 'Ville',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('ordre');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFaqs::route('/'),
            'create' => Pages\CreateFaq::route('/create'),
            'edit' => Pages\EditFaq::route('/{record}/edit'),
        ];
    }
}
