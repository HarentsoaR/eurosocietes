<?php

namespace App\Filament\Resources\ContenuIaResource\Pages;

use App\Filament\Resources\ContenuIaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContenuIa extends EditRecord
{
    protected static string $resource = ContenuIaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
