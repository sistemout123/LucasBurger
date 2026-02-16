<?php

namespace App\Filament\Resources\TransacaoEstoques;

use App\Filament\Resources\TransacaoEstoques\Pages\CreateTransacaoEstoque;
use App\Filament\Resources\TransacaoEstoques\Pages\EditTransacaoEstoque;
use App\Filament\Resources\TransacaoEstoques\Pages\ListTransacaoEstoques;
use App\Filament\Resources\TransacaoEstoques\Schemas\TransacaoEstoqueForm;
use App\Filament\Resources\TransacaoEstoques\Tables\TransacaoEstoquesTable;
use App\Models\TransacaoEstoque;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TransacaoEstoqueResource extends Resource
{
    protected static ?string $model = TransacaoEstoque::class;

    protected static ?string $modelLabel = 'Transação';
    protected static ?string $pluralModelLabel = 'Transações';
    protected static \UnitEnum|string|null $navigationGroup = 'Estoque';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $recordTitleAttribute = 'notas';

    public static function form(Schema $schema): Schema
    {
        return TransacaoEstoqueForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransacaoEstoquesTable::configure($table);
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
            'index' => ListTransacaoEstoques::route('/'),
            'create' => CreateTransacaoEstoque::route('/create'),
            'edit' => EditTransacaoEstoque::route('/{record}/edit'),
        ];
    }
}
