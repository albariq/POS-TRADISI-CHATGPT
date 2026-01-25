<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use App\Support\OutletContext;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentSales extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $outletId = OutletContext::id();

        return $table
            ->heading('Penjualan Terbaru')
            ->query($this->query($outletId))
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('receipt_number')
                    ->label('No. Struk')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total')
                    ->money('IDR', true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d-m-Y H:i'),
            ]);
    }

    protected function query(?int $outletId): Builder
    {
        if (! $outletId) {
            return Sale::query()->whereRaw('1=0');
        }

        return Sale::query()
            ->where('outlet_id', $outletId)
            ->where('status', 'paid');
    }
}
