<?php

namespace App\Filament\Resources\TransacaoEstoques\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TransacaoEstoqueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tipo_id')
                    ->required(),
                TextInput::make('product_id')
                    ->numeric(),
                TextInput::make('quantidade_produtos')
                    ->numeric(),
                TextInput::make('doc_referencia'),
                TextInput::make('solicitado_por')
                    ->required()
                    ->numeric(),
                TextInput::make('autorizado_por')
                    ->numeric(),
                Textarea::make('notas')
                    ->columnSpanFull(),
            ]);
    }
}
