<?php

namespace App\Filament\Resources\CampanhaInventarios\Pages;

use App\Filament\Resources\CampanhaInventarios\CampanhaInventarioResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCampanhaInventario extends EditRecord
{
    protected static string $resource = CampanhaInventarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
