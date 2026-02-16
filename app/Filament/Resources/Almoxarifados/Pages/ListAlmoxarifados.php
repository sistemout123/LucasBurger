<?php

namespace App\Filament\Resources\Almoxarifados\Pages;

use App\Filament\Resources\Almoxarifados\AlmoxarifadoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAlmoxarifados extends ListRecords
{
    protected static string $resource = AlmoxarifadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
