<?php

namespace App\Filament\Resources\EntrepriseResource\RelationManagers;

use App\Models\Section;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sectionOverrides';

    protected static ?string $title = 'Blocs visibles de la fiche';

    protected static ?string $modelLabel = 'bloc';

    protected static ?string $pluralModelLabel = 'blocs';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('section_id')
                    ->label('Section')
                    ->options(fn (): array => Section::query()->orderBy('ordre')->pluck('libelle', 'id')->all())
                    ->required(),
                Forms\Components\TextInput::make('position')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('visible')
                    ->label('Visible sur cette fiche')
                    ->default(true),
            ])
            ->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('section.libelle')
                    ->label('Section')
                    ->sortable(),
                Tables\Columns\TextColumn::make('section.ordre')
                    ->label('Ordre canonique')
                    ->sortable(),
                Tables\Columns\TextInputColumn::make('position')
                    ->label('Position')
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('visible')
                    ->label('Visible'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('position');
    }
}
