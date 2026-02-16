<?php

namespace App\Filament\Resources\CampanhaInventarios\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CampanhaInventarioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('titulo')
                    ->required(),
                TextInput::make('tipo')
                    ->required()
                    ->default('GERAL'),
                TextInput::make('status')
                    ->required()
                    ->default('ABERTA'),
                DateTimePicker::make('encerrado_em'),
            ]);
    }
}
