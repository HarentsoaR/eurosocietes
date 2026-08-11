<?php

namespace App\Filament\Resources\PubliciteResource\Pages;

use App\Filament\Resources\PubliciteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPublicite extends EditRecord
{
    protected static string $resource = PubliciteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
