<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use App\Models\Supplier;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema(self::getComponents());
    }

    public static function getComponents(): array
    {
        return [
            TextInput::make('name')
                ->label('Razão Social / Nome')
                ->required()
                ->maxLength(255),
            TextInput::make('cnpj_cpf')
                ->label('CNPJ / CPF')
                ->maxLength(20),
            TextInput::make('contact_name')
                ->label('Nome do Contato')
                ->maxLength(255),
            TextInput::make('phone')
                ->label('Telefone')
                ->tel()
                ->maxLength(255),
            TextInput::make('email')
                ->label('Email')
                ->email()
                ->maxLength(255),
            Toggle::make('is_active')
                ->label('Ativo')
                ->default(true),
        ];
    }
}
