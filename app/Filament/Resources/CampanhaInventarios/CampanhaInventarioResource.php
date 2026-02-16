<?php

namespace App\Filament\Resources\CampanhaInventarios;

use App\Filament\Resources\CampanhaInventarios\Pages\CreateCampanhaInventario;
use App\Filament\Resources\CampanhaInventarios\Pages\EditCampanhaInventario;
use App\Filament\Resources\CampanhaInventarios\Pages\ListCampanhaInventarios;
use App\Filament\Resources\CampanhaInventarios\Schemas\CampanhaInventarioForm;
use App\Filament\Resources\CampanhaInventarios\Tables\CampanhaInventariosTable;
use App\Models\CampanhaInventario;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CampanhaInventarioResource extends Resource
{
    protected static ?string $model = CampanhaInventario::class;

    protected static ?string $modelLabel = 'Inventário';
    protected static ?string $pluralModelLabel = 'Inventários';
    protected static \UnitEnum|string|null $navigationGroup = 'Auditoria';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $recordTitleAttribute = 'titulo';

    public static function form(Schema $schema): Schema
    {
        return CampanhaInventarioForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CampanhaInventariosTable::configure($table);
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
            'index' => ListCampanhaInventarios::route('/'),
            'create' => CreateCampanhaInventario::route('/create'),
            'edit' => EditCampanhaInventario::route('/{record}/edit'),
        ];
    }
}
