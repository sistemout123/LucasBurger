<?php

namespace App\Filament\Resources\SaldoEstoques\Pages;

use App\Filament\Resources\SaldoEstoques\SaldoEstoqueResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSaldoEstoque extends EditRecord
{
    protected static string $resource = SaldoEstoqueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
