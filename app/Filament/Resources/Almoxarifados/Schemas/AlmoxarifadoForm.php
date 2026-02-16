<?php

namespace App\Filament\Resources\Almoxarifados\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AlmoxarifadoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('codigo')
                    ->required(),
                TextInput::make('nome')
                    ->required(),
                Textarea::make('endereco')
                    ->columnSpanFull(),
                Toggle::make('esta_ativo')
                    ->required(),
            ]);
    }
}
