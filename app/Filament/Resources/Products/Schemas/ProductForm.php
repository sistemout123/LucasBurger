<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Ingredient;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome do Produto')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->label('Preço de Venda')
                    ->required()
                    ->numeric()
                    ->prefix('R$'),
                Toggle::make('is_active')
                    ->label('Ativo')
                    ->required()
                    ->default(true),

                Section::make('Ficha Técnica')
                    ->description('Adicione os ingredientes para calcular o custo do produto.')
                    ->schema([
                        Repeater::make('productIngredients')
                            ->relationship()
                            ->schema([
                                Select::make('ingredient_id')
                                    ->label('Ingrediente')
                                    ->options(Ingredient::all()->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $ingredient = Ingredient::find($state);
                                        if ($ingredient) {
                                            $set('unit_cost', $ingredient->unit_cost);
                                            $quantity = (float) $get('quantity') ?: 0;
                                            $set('total_cost', $quantity * $ingredient->unit_cost);
                                        }
                                    })
                                    ->columnSpan(4),

                                TextInput::make('quantity')
                                    ->label('Quantidade')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $unitCost = (float) $get('unit_cost') ?: 0;
                                        $set('total_cost', (float) $state * $unitCost);
                                    })
                                    ->columnSpan(2),

                                TextInput::make('unit_cost')
                                    ->label('Custo Unit.')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->numeric()
                                    ->prefix('R$')
                                    ->columnSpan(2),

                                TextInput::make('total_cost')
                                    ->label('Total')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->numeric()
                                    ->prefix('R$')
                                    ->columnSpan(2),
                            ])
                            ->columns(10)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $total = 0;
                                foreach ($get('productIngredients') as $item) {
                                    $total += (float) ($item['total_cost'] ?? 0);
                                }
                                $set('calculated_cost', $total);
                            }),

                        Placeholder::make('suggested_cost')
                            ->label('Custo Total Estimado')
                            ->content(function (Get $get) {
                                $total = 0;
                                $ingredients = $get('productIngredients') ?? [];
                                foreach ($ingredients as $item) {
                                    $total += (float) ($item['total_cost'] ?? 0);
                                }
                                return 'R$ ' . number_format($total, 2, ',', '.');
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
