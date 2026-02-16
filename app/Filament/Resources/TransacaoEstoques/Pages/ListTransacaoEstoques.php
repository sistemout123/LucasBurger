<?php

namespace App\Filament\Resources\TransacaoEstoques\Pages;

use App\Filament\Resources\TransacaoEstoques\TransacaoEstoqueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTransacaoEstoques extends ListRecords
{
    protected static string $resource = TransacaoEstoqueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
