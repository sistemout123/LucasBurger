<?php

namespace App\Filament\Resources\SaldoEstoques\Pages;

use App\Filament\Resources\SaldoEstoques\SaldoEstoqueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSaldoEstoques extends ListRecords
{
    protected static string $resource = SaldoEstoqueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
