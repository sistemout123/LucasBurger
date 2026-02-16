<?php

namespace App\Filament\Resources\CampanhaInventarios\Pages;

use App\Filament\Resources\CampanhaInventarios\CampanhaInventarioResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCampanhaInventarios extends ListRecords
{
    protected static string $resource = CampanhaInventarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
