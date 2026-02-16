<?php

namespace App\Filament\Resources\SaldoEstoques;

use App\Filament\Resources\SaldoEstoques\Pages\CreateSaldoEstoque;
use App\Filament\Resources\SaldoEstoques\Pages\EditSaldoEstoque;
use App\Filament\Resources\SaldoEstoques\Pages\ListSaldoEstoques;
use App\Filament\Resources\SaldoEstoques\Schemas\SaldoEstoqueForm;
use App\Filament\Resources\SaldoEstoques\Tables\SaldoEstoquesTable;
use App\Models\SaldoEstoque;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SaldoEstoqueResource extends Resource
{
    protected static ?string $model = SaldoEstoque::class;

    protected static ?string $modelLabel = 'Saldo de Estoque';
    protected static ?string $pluralModelLabel = 'Saldos de Estoque';
    protected static \UnitEnum|string|null $navigationGroup = 'Estoque';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $recordTitleAttribute = 'quantidade';

    public static function form(Schema $schema): Schema
    {
        return SaldoEstoqueForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SaldoEstoquesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSaldoEstoques::route('/'),
            'create' => CreateSaldoEstoque::route('/create'),
            'edit' => EditSaldoEstoque::route('/{record}/edit'),
        ];
    }
}
