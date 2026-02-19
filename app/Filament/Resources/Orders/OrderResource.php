<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages\ManageOrders;
use App\Models\Order;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'statusyes';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('table_id')
                    ->numeric(),
                TextInput::make('ticket_number'),
                TextInput::make('status')
                    ->required()
                    ->default('open'),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('ticket_number')
            ->columns([
                TextColumn::make('id')
                    ->label('Pedido #')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('table.number')
                    ->label('Mesa')
                    ->placeholder('Balcão/Delivery')
                    ->sortable(),
                TextColumn::make('ticket_number')
                    ->label('Senha/Comanda')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => \App\Enums\OrderStatus::tryFrom($state)?->getLabel() ?? $state)
                    ->colors([
                        'warning' => 'open',
                        'primary' => 'preparing',
                        'success' => 'ready',
                        'gray' => 'paid',
                        'danger' => 'canceled',
                    ])
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Total (R$)')
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Aberto em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageOrders::route('/'),
        ];
    }
}
