<?php

namespace App\Filament\Resources\UtilisateurResource\Pages;

use App\Filament\Resources\UtilisateurResource;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;

class EditUtilisateur extends EditRecord
{
    protected static string $resource = UtilisateurResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->hidden(fn (): bool => $this->record->id === Filament::auth()->id()),
        ];
    }
}
