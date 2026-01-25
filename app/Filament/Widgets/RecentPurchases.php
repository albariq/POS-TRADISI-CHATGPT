<?php

namespace App\Filament\Widgets;

use App\Models\Purchase;
use App\Support\OutletContext;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentPurchases extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $outletId = OutletContext::id();

        return $table
            ->heading('Pembelian Terbaru')
            ->query($this->query($outletId))
            ->defaultSort('purchased_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice')
                    ->searchable(),
                Tables\Columns\TextColumn::make('supplier_name')
                    ->label('Pemasok')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total_cost')
                    ->label('Total')
                    ->money('IDR', true),
                Tables\Columns\TextColumn::make('purchased_at')
                    ->label('Tanggal')
                    ->dateTime('d-m-Y H:i'),
            ]);
    }

    protected function query(?int $outletId): Builder
    {
        if (! $outletId) {
            return Purchase::query()->whereRaw('1=0');
        }

        return Purchase::query()
            ->where('outlet_id', $outletId);
    }
}
