<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UtilisateurResource\Pages;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Password;

class UtilisateurResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Utilisateurs';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $modelLabel = 'utilisateur';

    protected static ?string $pluralModelLabel = 'utilisateurs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Compte')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->password()
                            ->revealable()
                            ->dehydrated(false)
                            ->same('password'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Rôles')
                    ->schema([
                        Forms\Components\CheckboxList::make('roles')
                            ->relationship('roles', 'name')
                            ->descriptions([
                                'admin' => 'Accès complet au back-office',
                                'editeur' => 'Gestion des contenus',
                                'entreprise' => 'Espace entreprise',
                                'utilisateur' => 'Compte visiteur',
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Rôles')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'Admin',
                        'editeur' => 'Éditeur',
                        'entreprise' => 'Entreprise',
                        default => 'Utilisateur',
                    }),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label('Vérifié')
                    ->dateTime()
                    ->placeholder('Non vérifié'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name'),
            ])
            ->actions([
                Tables\Actions\Action::make('send_reset_link')
                    ->label('Envoyer un lien de réinitialisation')
                    ->icon('heroicon-o-key')
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $status = Password::broker()->sendResetLink(['email' => $record->email]);
                        $label = $status === Password::RESET_LINK_SENT ? 'Lien envoyé' : 'Impossible d’envoyer le lien';
                        Notification::make()->title($label)->{$status === Password::RESET_LINK_SENT ? 'success' : 'warning'}()->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn (User $record): bool => $record->id === Filament::auth()->id()),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUtilisateurs::route('/'),
            'create' => Pages\CreateUtilisateur::route('/create'),
            'edit' => Pages\EditUtilisateur::route('/{record}/edit'),
        ];
    }
}
