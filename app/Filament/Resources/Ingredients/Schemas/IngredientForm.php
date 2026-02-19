<?php

namespace App\Filament\Resources\Ingredients\Schemas;

use App\Models\Ingredient;
use App\Models\UnitOfMeasure;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class IngredientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::getComponents());
    }

    public static function getComponents(bool $withRecipe = true): array
    {
        $components = [
            Section::make('Dados Gerais')
                ->icon('heroicon-o-cube')
                ->columns(3)
                ->schema([
                    TextInput::make('name')
                        ->label('Nome')
                        ->required()
                        ->columnSpan(2),
                    Select::make('tipo')
                        ->label('Tipo')
                        ->options([
                            Ingredient::TIPO_COMPRADO => '🛒 Comprado',
                            Ingredient::TIPO_PREPARACAO => '🍳 Preparação',
                        ])
                        ->default(Ingredient::TIPO_COMPRADO)
                        ->required()
                        ->live()
                        ->columnSpan(1),
                    Select::make('unit_of_measure_id')
                        ->label('Unidade de Medida')
                        ->options(UnitOfMeasure::pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->createOptionForm([\App\Filament\Resources\UnitOfMeasures\Schemas\UnitOfMeasureForm::class, 'getComponents']) // Optional: if UoM form exists
                        ->createOptionUsing(function (array $data) {
                            return UnitOfMeasure::create($data)->id;
                        }),
                    TextInput::make('unit_cost')
                        ->label('Custo Unitário')
                        ->numeric()
                        ->required()
                        ->default(0)
                        ->prefix('R$')
                        ->helperText(fn(Get $get) => $get('tipo') === Ingredient::TIPO_PREPARACAO
                            ? 'Será recalculado com base na receita'
                            : 'Custo de compra por unidade'),
                    Toggle::make('controle_lote')
                        ->label('Controlar Lote')
                        ->helperText('Rastrear nº de lote, validade e fornecedor'),
                ]),

            Section::make('Estoque')
                ->icon('heroicon-o-chart-bar')
                ->columns(3)
                ->schema([
                    TextInput::make('current_stock')
                        ->label('Estoque Atual')
                        ->numeric()
                        ->default(0),
                    TextInput::make('min_stock')
                        ->label('Estoque Mínimo')
                        ->numeric()
                        ->default(0),
                    TextInput::make('estoque_maximo')
                        ->label('Estoque Máximo')
                        ->numeric()
                        ->default(0),
                ]),
        ];

        if ($withRecipe) {
            $components[] = Section::make('Receita da Preparação')
                ->icon('heroicon-o-beaker')
                ->description('Defina os ingredientes base e quantidades para produzir esta preparação.')
                ->visible(fn(Get $get) => $get('tipo') === Ingredient::TIPO_PREPARACAO)
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('rendimento')
                            ->label('Rendimento da Receita')
                            ->helperText('Quantas unidades esta receita produz')
                            ->numeric()
                            ->required(fn(Get $get) => $get('tipo') === Ingredient::TIPO_PREPARACAO)
                            ->minValue(0.01)
                            ->columnSpan(1),
                        Placeholder::make('custo_producao_info')
                            ->label('Custo Total da Receita')
                            ->content(function (?Ingredient $record) {
                                if (!$record || !$record->isPreparacao()) {
                                    return 'Salve primeiro para calcular';
                                }
                                return 'R$ ' . number_format($record->custo_producao, 2, ',', '.');
                            })
                            ->columnSpan(1),
                        Placeholder::make('custo_unitario_info')
                            ->label('Custo por Unidade')
                            ->content(function (?Ingredient $record) {
                                if (!$record || !$record->isPreparacao()) {
                                    return 'Salve primeiro para calcular';
                                }
                                return 'R$ ' . number_format($record->custo_unitario_producao, 2, ',', '.');
                            })
                            ->columnSpan(1),
                    ]),
                    Repeater::make('receita')
                        ->label('Ingredientes da Receita')
                        ->relationship('receita')
                        ->schema([
                            Grid::make(3)->schema([
                                Select::make('ingrediente_filho_id')
                                    ->label('Ingrediente')
                                    ->options(
                                        Ingredient::where('tipo', Ingredient::TIPO_COMPRADO)
                                            ->orderBy('name')
                                            ->pluck('name', 'id')
                                    )
                                    ->searchable()
                                    ->required()
                                    ->columnSpan(2)
                                    ->createOptionForm(self::getComponents(false))
                                    ->createOptionUsing(function (array $data) {
                                        return Ingredient::create($data)->id;
                                    }),
                                TextInput::make('quantidade')
                                    ->label('Quantidade')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0.0001)
                                    ->columnSpan(1),
                            ]),
                        ])
                        ->addActionLabel('+ Adicionar Ingrediente')
                        ->reorderable(false)
                        ->defaultItems(0)
                        ->columns(1),
                ]);
        }

        return $components;
    }
}
