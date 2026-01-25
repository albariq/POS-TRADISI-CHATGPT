<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryStockResource\Pages;
use App\Models\InventoryStock;
use App\Support\OutletContext;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use BackedEnum;

class InventoryStockResource extends Resource
{
    protected static ?string $model = InventoryStock::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|UnitEnum|null $navigationGroup = 'Inventori';

    protected static ?string $navigationLabel = 'Stok';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('qty_grams')
                    ->label('Qty (g)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('min_qty_grams')
                    ->label('Min (g)')
                    ->numeric()
                    ->sortable(),
            ])
            ->actions([])
            ->paginationPageOptions([20, 50, 100]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('outlet_id', OutletContext::id());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventoryStocks::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
