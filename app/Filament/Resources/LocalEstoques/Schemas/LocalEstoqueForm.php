<?php

namespace App\Filament\Resources\LocalEstoques\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LocalEstoqueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('almoxarifado_id')
                    ->required()
                    ->numeric(),
                TextInput::make('codigo')
                    ->required(),
                TextInput::make('nome')
                    ->required(),
                TextInput::make('tipo')
                    ->required()
                    ->default('AMBIENTE'),
                Toggle::make('esta_ativo')
                    ->required(),
            ]);
    }
}
