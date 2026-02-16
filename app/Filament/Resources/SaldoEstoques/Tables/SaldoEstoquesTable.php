<?php

namespace App\Filament\Resources\SaldoEstoques\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SaldoEstoquesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ingredient.name')
                    ->label('Ingrediente')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('local.nome')
                    ->label('Local')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->local?->almoxarifado?->nome),
                TextColumn::make('lote.numero_lote')
                    ->label('Lote')
                    ->placeholder('Sem lote')
                    ->sortable(),
                TextColumn::make('status_estoque')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'DISPONIVEL' => 'success',
                        'QUARENTENA' => 'warning',
                        'BLOQUEADO' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('quantidade')
                    ->label('Quantidade')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->color(fn($state) => $state <= 0 ? 'danger' : null),
                TextColumn::make('ingredient.unitOfMeasure.acronym')
                    ->label('Un.'),
                TextColumn::make('ultima_movimentacao_em')
                    ->label('Última Mov.')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('local_id')
                    ->label('Local')
                    ->relationship('local', 'nome'),
            ])
            ->defaultSort('ingredient.name')
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
