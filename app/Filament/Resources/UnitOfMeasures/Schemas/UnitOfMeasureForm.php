<?php

namespace App\Filament\Resources\UnitOfMeasures\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UnitOfMeasureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('acronym')
                    ->required(),
            ]);
    }
}
