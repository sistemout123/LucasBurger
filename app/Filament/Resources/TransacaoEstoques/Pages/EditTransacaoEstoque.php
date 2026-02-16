<?php

namespace App\Filament\Resources\TransacaoEstoques\Pages;

use App\Filament\Resources\TransacaoEstoques\TransacaoEstoqueResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTransacaoEstoque extends EditRecord
{
    protected static string $resource = TransacaoEstoqueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
