<?php

namespace App\Filament\Widgets;

use App\Models\InventoryStock;
use App\Support\OutletContext;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LowStock extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $outletId = OutletContext::id();

        return $table
            ->heading('Stok Menipis')
            ->query($this->query($outletId))
            ->defaultSort('qty_grams', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable(),
                Tables\Columns\TextColumn::make('qty_grams')
                    ->label('Qty (g)')
                    ->numeric(),
                Tables\Columns\TextColumn::make('min_qty_grams')
                    ->label('Min (g)')
                    ->numeric(),
            ]);
    }

    protected function query(?int $outletId): Builder
    {
        if (! $outletId) {
            return InventoryStock::query()->whereRaw('1=0');
        }

        return InventoryStock::query()
            ->where('outlet_id', $outletId)
            ->where('min_qty_grams', '>', 0)
            ->whereColumn('qty_grams', '<=', 'min_qty_grams');
    }
}
