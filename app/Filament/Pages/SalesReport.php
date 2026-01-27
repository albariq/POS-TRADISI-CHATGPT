<?php

namespace App\Filament\Pages;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Support\OutletContext;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use BackedEnum;

class SalesReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Laporan Penjualan';

    public function getView(): string
    {
        return 'filament.pages.sales-report';
    }

    public function getSummaryProperty(): array
    {
        [$from, $to] = $this->getDateRange();
        $outletId = OutletContext::id();

        $summaryRow = Sale::where('outlet_id', $outletId)
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->where('status', 'paid')
            ->selectRaw('SUM(subtotal) as subtotal_sum')
            ->selectRaw('SUM(discount_total) as discount_sum')
            ->selectRaw('SUM(tax_total) as tax_sum')
            ->selectRaw('SUM(service_total) as service_sum')
            ->selectRaw('SUM(grand_total) as grand_sum')
            ->first();

        $cogsTotal = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.outlet_id', $outletId)
            ->where('sales.status', 'paid')
            ->whereBetween('sales.created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->sum('sale_items.cogs_total');

        $subtotal = (float) ($summaryRow->subtotal_sum ?? 0);
        $discount = (float) ($summaryRow->discount_sum ?? 0);
        $netSales = max(0, $subtotal - $discount);
        $grossProfit = $netSales - (float) $cogsTotal;
        $grossMargin = $netSales > 0 ? ($grossProfit / $netSales) * 100 : 0;

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'net_sales' => $netSales,
            'cogs' => (float) $cogsTotal,
            'gross_profit' => $grossProfit,
            'gross_margin' => $grossMargin,
            'tax' => (float) ($summaryRow->tax_sum ?? 0),
            'service' => (float) ($summaryRow->service_sum ?? 0),
            'grand_total' => (float) ($summaryRow->grand_sum ?? 0),
            'from' => $from,
            'to' => $to,
        ];
    }

    public function getByProductProperty()
    {
        [$from, $to] = $this->getDateRange();
        $outletId = OutletContext::id();

        return SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->where('sales.outlet_id', $outletId)
            ->where('sales.status', 'paid')
            ->whereBetween('sales.created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->groupBy('sale_items.product_id', 'products.name', 'categories.name')
            ->selectRaw('products.name as product_name')
            ->selectRaw('categories.name as category_name')
            ->selectRaw('SUM(sale_items.qty) as qty')
            ->selectRaw('SUM(sale_items.line_total) as net_sales')
            ->selectRaw('SUM(sale_items.cogs_total) as cogs_total')
            ->orderByDesc('net_sales')
            ->get()
            ->map(function ($row) {
                $grossProfit = (float) $row->net_sales - (float) $row->cogs_total;
                $grossMargin = (float) $row->net_sales > 0 ? ($grossProfit / (float) $row->net_sales) * 100 : 0;
                $row->gross_profit = $grossProfit;
                $row->gross_margin = $grossMargin;
                $row->category_name = $row->category_name ?: 'Uncategorized';
                return $row;
            });
    }

    public function getByCategoryProperty()
    {
        [$from, $to] = $this->getDateRange();
        $outletId = OutletContext::id();

        return SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->where('sales.outlet_id', $outletId)
            ->where('sales.status', 'paid')
            ->whereBetween('sales.created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->groupBy('categories.id', 'categories.name')
            ->selectRaw('categories.name as category_name')
            ->selectRaw('SUM(sale_items.qty) as qty')
            ->selectRaw('SUM(sale_items.line_total) as net_sales')
            ->selectRaw('SUM(sale_items.cogs_total) as cogs_total')
            ->orderByDesc('net_sales')
            ->get()
            ->map(function ($row) {
                $grossProfit = (float) $row->net_sales - (float) $row->cogs_total;
                $grossMargin = (float) $row->net_sales > 0 ? ($grossProfit / (float) $row->net_sales) * 100 : 0;
                $row->gross_profit = $grossProfit;
                $row->gross_margin = $grossMargin;
                $row->category_name = $row->category_name ?: 'Uncategorized';
                return $row;
            });
    }

    public function getByCashierProperty()
    {
        [$from, $to] = $this->getDateRange();
        $outletId = OutletContext::id();

        return SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->leftJoin('users', 'users.id', '=', 'sales.cashier_id')
            ->where('sales.outlet_id', $outletId)
            ->where('sales.status', 'paid')
            ->whereBetween('sales.created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->groupBy('sales.cashier_id', 'users.name')
            ->selectRaw('users.name as cashier_name')
            ->selectRaw('COUNT(DISTINCT sales.id) as transactions')
            ->selectRaw('SUM(sale_items.line_total) as net_sales')
            ->selectRaw('SUM(sale_items.cogs_total) as cogs_total')
            ->orderByDesc('net_sales')
            ->get()
            ->map(function ($row) {
                $grossProfit = (float) $row->net_sales - (float) $row->cogs_total;
                $grossMargin = (float) $row->net_sales > 0 ? ($grossProfit / (float) $row->net_sales) * 100 : 0;
                $row->gross_profit = $grossProfit;
                $row->gross_margin = $grossMargin;
                $row->cashier_name = $row->cashier_name ?: 'Unknown';
                return $row;
            });
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Sale::query()
                    ->where('outlet_id', OutletContext::id())
                    ->where('status', 'paid')
                    ->withSum('items as cogs_total', 'cogs_total')
            )
            ->columns([
                Tables\Columns\TextColumn::make('receipt_number')
                    ->label('No. Struk')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('subtotal')
                    ->money('IDR', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('net_sales')
                    ->label('Net Sales')
                    ->state(fn (Sale $record): float => max(0, (float) $record->subtotal - (float) $record->discount_total))
                    ->money('IDR', true),
                Tables\Columns\TextColumn::make('cogs_total')
                    ->label('COGS')
                    ->money('IDR', true),
                Tables\Columns\TextColumn::make('gross_profit')
                    ->label('Gross Profit')
                    ->state(function (Sale $record): float {
                        $netSales = max(0, (float) $record->subtotal - (float) $record->discount_total);
                        return $netSales - (float) ($record->cogs_total ?? 0);
                    })
                    ->money('IDR', true),
                Tables\Columns\TextColumn::make('gross_margin')
                    ->label('Gross Margin')
                    ->state(function (Sale $record): float {
                        $netSales = max(0, (float) $record->subtotal - (float) $record->discount_total);
                        if ($netSales <= 0) {
                            return 0;
                        }
                        $grossProfit = $netSales - (float) ($record->cogs_total ?? 0);
                        return round(($grossProfit / $netSales) * 100, 2);
                    })
                    ->suffix('%'),
                Tables\Columns\TextColumn::make('tax_total')
                    ->label('Pajak')
                    ->money('IDR', true),
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total')
                    ->money('IDR', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('date')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('to'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['to'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date)
                            );
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected function getDateRange(): array
    {
        $state = $this->getTableFilterState('date') ?? [];
        $from = $state['from'] ?? now()->startOfMonth()->toDateString();
        $to = $state['to'] ?? now()->toDateString();

        return [$from, $to];
    }
}
