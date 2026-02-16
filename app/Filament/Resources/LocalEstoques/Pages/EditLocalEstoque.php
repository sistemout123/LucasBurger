<?php

namespace App\Filament\Resources\LocalEstoques\Pages;

use App\Filament\Resources\LocalEstoques\LocalEstoqueResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLocalEstoque extends EditRecord
{
    protected static string $resource = LocalEstoqueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
