<?php

namespace App\Filament\Pages;

use App\Models\InventoryStock;
use App\Models\StockMovement;
use App\Filament\Pages\InventoryReport\Widgets\InventorySummary;
use App\Support\OutletContext;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use BackedEnum;

class InventoryReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Laporan Stok';

    public function getView(): string
    {
        return 'filament.pages.inventory-report';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            InventorySummary::class,
        ];
    }

    public function getWidgetData(): array
    {
        return [
            'summary' => $this->getSummaryProperty(),
        ];
    }

    public function getSummaryProperty(): array
    {
        [$start, $end] = $this->getMonthRange();
        $outletId = OutletContext::id();

        if (! $outletId) {
            return [
                'total_out' => 0,
                'net' => 0,
                'top_out' => [],
            ];
        }

        $totalOut = (float) StockMovement::query()
            ->where('outlet_id', $outletId)
            ->where('type', 'out')
            ->whereBetween('created_at', [$start, $end])
            ->sum('qty_grams');

        $totalIn = (float) StockMovement::query()
            ->where('outlet_id', $outletId)
            ->where('type', 'in')
            ->whereBetween('created_at', [$start, $end])
            ->sum('qty_grams');

        $topOut = StockMovement::query()
            ->join('products', 'products.id', '=', 'stock_movements.product_id')
            ->where('stock_movements.outlet_id', $outletId)
            ->where('stock_movements.type', 'out')
            ->whereBetween('stock_movements.created_at', [$start, $end])
            ->groupBy('stock_movements.product_id', 'products.name')
            ->selectRaw('products.name as product_name')
            ->selectRaw('SUM(stock_movements.qty_grams) as total_out')
            ->orderByDesc('total_out')
            ->limit(3)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->product_name,
                'total_out' => (float) $row->total_out,
            ])
            ->all();

        return [
            'total_out' => $totalOut,
            'net' => $totalIn - $totalOut,
            'top_out' => $topOut,
            'from' => $start,
            'to' => $end,
        ];
    }

    public function table(Table $table): Table
    {
        [$start, $end] = $this->getMonthRange();

        return $table
            ->query(
                InventoryStock::query()
                    ->where('outlet_id', OutletContext::id())
                    ->with('product', 'variant')
                    ->select('inventory_stocks.*')
                    ->selectSub(function ($query) use ($start, $end) {
                        $query
                            ->from('stock_movements')
                            ->selectRaw('COALESCE(SUM(qty_grams), 0)')
                            ->whereColumn('stock_movements.outlet_id', 'inventory_stocks.outlet_id')
                            ->whereColumn('stock_movements.product_id', 'inventory_stocks.product_id')
                            ->where(function ($query) {
                                $query
                                    ->whereColumn('stock_movements.product_variant_id', 'inventory_stocks.product_variant_id')
                                    ->orWhere(function ($query) {
                                        $query
                                            ->whereNull('stock_movements.product_variant_id')
                                            ->whereNull('inventory_stocks.product_variant_id');
                                    });
                            })
                            ->where('stock_movements.type', 'in')
                            ->whereBetween('stock_movements.created_at', [$start, $end]);
                    }, 'total_in_grams')
                    ->selectSub(function ($query) use ($start, $end) {
                        $query
                            ->from('stock_movements')
                            ->selectRaw('COALESCE(SUM(qty_grams), 0)')
                            ->whereColumn('stock_movements.outlet_id', 'inventory_stocks.outlet_id')
                            ->whereColumn('stock_movements.product_id', 'inventory_stocks.product_id')
                            ->where(function ($query) {
                                $query
                                    ->whereColumn('stock_movements.product_variant_id', 'inventory_stocks.product_variant_id')
                                    ->orWhere(function ($query) {
                                        $query
                                            ->whereNull('stock_movements.product_variant_id')
                                            ->whereNull('inventory_stocks.product_variant_id');
                                    });
                            })
                            ->where('stock_movements.type', 'out')
                            ->whereBetween('stock_movements.created_at', [$start, $end]);
                    }, 'total_out_grams')
            )
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
                Tables\Columns\TextColumn::make('total_in_grams')
                    ->label('Masuk (g)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_out_grams')
                    ->label('Keluar (g)')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('month')
                    ->form([
                        Select::make('month')
                            ->label('Bulan')
                            ->options([
                                '01' => 'Januari',
                                '02' => 'Februari',
                                '03' => 'Maret',
                                '04' => 'April',
                                '05' => 'Mei',
                                '06' => 'Juni',
                                '07' => 'Juli',
                                '08' => 'Agustus',
                                '09' => 'September',
                                '10' => 'Oktober',
                                '11' => 'November',
                                '12' => 'Desember',
                            ])
                            ->default(now()->format('m')),
                        Select::make('year')
                            ->label('Tahun')
                            ->options($this->getYearOptions())
                            ->default(now()->format('Y')),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query),
            ]);
    }

    private function getMonthRange(): array
    {
        $state = $this->getTableFilterState('month') ?? [];
        $month = $state['month'] ?? now()->format('m');
        $year = $state['year'] ?? now()->format('Y');

        $start = Carbon::createFromFormat('Y-m-d', $year.'-'.$month.'-01')->startOfDay();
        $end = $start->copy()->endOfMonth()->endOfDay();

        return [$start, $end];
    }

    private function getYearOptions(): array
    {
        $current = (int) now()->format('Y');
        $years = range($current - 4, $current + 1);
        $options = [];
        foreach ($years as $year) {
            $options[(string) $year] = (string) $year;
        }

        return $options;
    }
}
