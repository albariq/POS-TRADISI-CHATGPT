<?php

namespace App\Filament\Widgets;

use App\Models\StockMovement;
use App\Support\OutletContext;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentStockMovements extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $outletId = OutletContext::id();

        return $table
            ->heading('Pergerakan Stok Terbaru')
            ->query($this->query($outletId))
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d-m-Y H:i'),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe'),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable(),
                Tables\Columns\TextColumn::make('qty_grams')
                    ->label('Qty (g)')
                    ->numeric(),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Alasan')
                    ->toggleable(),
            ]);
    }

    protected function query(?int $outletId): Builder
    {
        if (! $outletId) {
            return StockMovement::query()->whereRaw('1=0');
        }

        return StockMovement::query()
            ->where('outlet_id', $outletId);
    }
}
