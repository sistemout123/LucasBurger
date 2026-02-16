<?php

namespace App\Filament\Resources\Almoxarifados;

use App\Filament\Resources\Almoxarifados\Pages\CreateAlmoxarifado;
use App\Filament\Resources\Almoxarifados\Pages\EditAlmoxarifado;
use App\Filament\Resources\Almoxarifados\Pages\ListAlmoxarifados;
use App\Filament\Resources\Almoxarifados\Schemas\AlmoxarifadoForm;
use App\Filament\Resources\Almoxarifados\Tables\AlmoxarifadosTable;
use App\Models\Almoxarifado;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AlmoxarifadoResource extends Resource
{
    protected static ?string $model = Almoxarifado::class;

    protected static ?string $modelLabel = 'Almoxarifado';
    protected static ?string $pluralModelLabel = 'Almoxarifados';
    protected static \UnitEnum|string|null $navigationGroup = 'Estoque';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $recordTitleAttribute = 'nome';

    public static function form(Schema $schema): Schema
    {
        return AlmoxarifadoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AlmoxarifadosTable::configure($table);
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
            'index' => ListAlmoxarifados::route('/'),
            'create' => CreateAlmoxarifado::route('/create'),
            'edit' => EditAlmoxarifado::route('/{record}/edit'),
        ];
    }
}
