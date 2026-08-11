<?php

namespace App\Filament\Resources\ContenuIaResource\Pages;

use App\Filament\Resources\ContenuIaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContenusIa extends ListRecords
{
    protected static string $resource = ContenuIaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nouveau contenu IA'),
        ];
    }
}
