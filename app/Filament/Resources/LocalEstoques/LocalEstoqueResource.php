<?php

namespace App\Filament\Resources\LocalEstoques;

use App\Filament\Resources\LocalEstoques\Pages\CreateLocalEstoque;
use App\Filament\Resources\LocalEstoques\Pages\EditLocalEstoque;
use App\Filament\Resources\LocalEstoques\Pages\ListLocalEstoques;
use App\Filament\Resources\LocalEstoques\Schemas\LocalEstoqueForm;
use App\Filament\Resources\LocalEstoques\Tables\LocalEstoquesTable;
use App\Models\LocalEstoque;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LocalEstoqueResource extends Resource
{
    protected static ?string $model = LocalEstoque::class;

    protected static ?string $modelLabel = 'Local de Estoque';
    protected static ?string $pluralModelLabel = 'Locais de Estoque';
    protected static \UnitEnum|string|null $navigationGroup = 'Estoque';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $recordTitleAttribute = 'nome';

    public static function form(Schema $schema): Schema
    {
        return LocalEstoqueForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LocalEstoquesTable::configure($table);
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
            'index' => ListLocalEstoques::route('/'),
            'create' => CreateLocalEstoque::route('/create'),
            'edit' => EditLocalEstoque::route('/{record}/edit'),
        ];
    }
}
