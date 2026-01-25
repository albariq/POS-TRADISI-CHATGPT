<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockMovementResource\Pages;
use App\Models\StockMovement;
use App\Support\OutletContext;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use BackedEnum;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static string|UnitEnum|null $navigationGroup = 'Inventori';

    protected static ?string $navigationLabel = 'Pergerakan Stok';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable(),
                Tables\Columns\TextColumn::make('variant.name')
                    ->label('Varian')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('qty_grams')
                    ->label('Qty (g)')
                    ->numeric(),
                Tables\Columns\TextColumn::make('before_qty_grams')
                    ->label('Sebelum')
                    ->numeric(),
                Tables\Columns\TextColumn::make('after_qty_grams')
                    ->label('Sesudah')
                    ->numeric(),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Alasan')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('User')
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('outlet_id', OutletContext::id());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockMovements::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
