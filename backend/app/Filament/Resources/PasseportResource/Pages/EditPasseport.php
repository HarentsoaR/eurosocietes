<?php

namespace App\Filament\Resources\PasseportResource\Pages;

use App\Filament\Resources\PasseportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPasseport extends EditRecord
{
    protected static string $resource = PasseportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
