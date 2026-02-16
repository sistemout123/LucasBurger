<?php

namespace App\Filament\Resources\TransacaoEstoques\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Base\Filters\SelectFilter;
use Filament\Tables\Table;

class TransacaoEstoquesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('tipo.descricao')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn($record) => match ($record->tipo?->tipo_impacto) {
                        '+' => 'success',
                        '-' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('product.name')
                    ->label('Produto')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('quantidade_produtos')
                    ->label('Qtd Prod.')
                    ->numeric()
                    ->placeholder('—'),
                TextColumn::make('doc_referencia')
                    ->label('Doc. Ref.')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('solicitante.name')
                    ->label('Solicitante')
                    ->sortable(),
                TextColumn::make('autorizador.name')
                    ->label('Autorizado por')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
