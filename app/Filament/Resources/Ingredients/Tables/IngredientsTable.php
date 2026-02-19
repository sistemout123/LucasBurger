<?php

namespace App\Filament\Resources\Ingredients\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\Ingredient;

class IngredientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->label('Nome'),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->colors([
                        'success' => Ingredient::TIPO_COMPRADO,
                        'warning' => Ingredient::TIPO_PREPARACAO,
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        Ingredient::TIPO_COMPRADO => '🛒 Comprado',
                        Ingredient::TIPO_PREPARACAO => '🍳 Preparação',
                        default => $state,
                    }),
                TextColumn::make('unit_of_measure_id')
                    ->label('Un. Medida')
                    ->formatStateUsing(fn($state) => \App\Models\UnitOfMeasure::find($state)?->acronym ?? $state)
                    ->sortable(),
                TextColumn::make('unit_cost')
                    ->label('Custo Unit.')
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('current_stock')
                    ->label('Estoque')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('min_stock')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
