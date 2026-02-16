<?php

namespace App\Filament\Resources\Almoxarifados\Pages;

use App\Filament\Resources\Almoxarifados\AlmoxarifadoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAlmoxarifado extends EditRecord
{
    protected static string $resource = AlmoxarifadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
