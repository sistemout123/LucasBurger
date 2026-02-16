<?php

namespace App\Filament\Resources\LocalEstoques\Pages;

use App\Filament\Resources\LocalEstoques\LocalEstoqueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLocalEstoques extends ListRecords
{
    protected static string $resource = LocalEstoqueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
