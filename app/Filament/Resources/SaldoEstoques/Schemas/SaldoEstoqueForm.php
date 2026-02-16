<?php

namespace App\Filament\Resources\SaldoEstoques\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SaldoEstoqueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('ingredient_id')
                    ->required()
                    ->numeric(),
                TextInput::make('local_id')
                    ->required()
                    ->numeric(),
                TextInput::make('lote_id')
                    ->numeric(),
                TextInput::make('status_estoque')
                    ->required()
                    ->default('DISPONIVEL'),
                TextInput::make('quantidade')
                    ->required()
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('ultima_movimentacao_em'),
            ]);
    }
}
