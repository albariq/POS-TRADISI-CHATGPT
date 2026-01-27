<?php

namespace App\Filament\Pages;

use App\Models\Outlet;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Support\OutletContext;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
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
        [$scopeAllOutlets, $outletFilterIds, $selectedOutletId] = $this->getOutletScope();

        $summaryRow = Sale::query()
            ->when(
                $scopeAllOutlets,
                fn (Builder $query) => $query->whereIn('outlet_id', $outletFilterIds),
                fn (Builder $query) => $query->where('outlet_id', $selectedOutletId)
            )
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
            ->when(
                $scopeAllOutlets,
                fn (Builder $query) => $query->whereIn('sales.outlet_id', $outletFilterIds),
                fn (Builder $query) => $query->where('sales.outlet_id', $selectedOutletId)
            )
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
        [$scopeAllOutlets, $outletFilterIds, $selectedOutletId] = $this->getOutletScope();

        return SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->when(
                $scopeAllOutlets,
                fn (Builder $query) => $query->whereIn('sales.outlet_id', $outletFilterIds),
                fn (Builder $query) => $query->where('sales.outlet_id', $selectedOutletId)
            )
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
        [$scopeAllOutlets, $outletFilterIds, $selectedOutletId] = $this->getOutletScope();

        return SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->when(
                $scopeAllOutlets,
                fn (Builder $query) => $query->whereIn('sales.outlet_id', $outletFilterIds),
                fn (Builder $query) => $query->where('sales.outlet_id', $selectedOutletId)
            )
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
        [$scopeAllOutlets, $outletFilterIds, $selectedOutletId] = $this->getOutletScope();

        return SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->leftJoin('users', 'users.id', '=', 'sales.cashier_id')
            ->when(
                $scopeAllOutlets,
                fn (Builder $query) => $query->whereIn('sales.outlet_id', $outletFilterIds),
                fn (Builder $query) => $query->where('sales.outlet_id', $selectedOutletId)
            )
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
        [$scopeAllOutlets, $outletFilterIds, $selectedOutletId] = $this->getOutletScope();

        return $table
            ->query(
                Sale::query()
                    ->when(
                        $scopeAllOutlets,
                        fn (Builder $query) => $query->whereIn('outlet_id', $outletFilterIds),
                        fn (Builder $query) => $query->where('outlet_id', $selectedOutletId)
                    )
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
                Tables\Filters\Filter::make('outlet')
                    ->form([
                        Select::make('outlet_id')
                            ->label('Cabang')
                            ->options(function (): array {
                                $user = Auth::user();
                                $outlets = $user
                                    ? $user->outlets()
                                        ->where('outlets.is_active', true)
                                        ->orderBy('outlets.name')
                                        ->get(['outlets.id as id', 'outlets.code as code', 'outlets.name as name'])
                                    : Outlet::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']);

                                $options = ['all' => 'Semua Cabang'];
                                foreach ($outlets as $outlet) {
                                    $label = ($outlet->code ? $outlet->code.' - ' : '').$outlet->name;
                                    $options[(string) $outlet->id] = $label;
                                }

                                return $options;
                            })
                            ->default((string) (OutletContext::id() ?? 'all'))
                            ->searchable(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $outletId = $data['outlet_id'] ?? null;
                        if (! $outletId || $outletId === 'all') {
                            return $query;
                        }

                        return $query->where('outlet_id', (int) $outletId);
                    }),
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

    protected function getOutletScope(): array
    {
        $user = Auth::user();
        $userOutlets = $user
            ? $user->outlets()->where('is_active', true)->orderBy('name')->get(['outlets.id as id'])
            : Outlet::where('is_active', true)->orderBy('name')->get(['id']);

        $allowedOutletIds = $userOutlets->pluck('id')->all();
        $activeOutletId = OutletContext::id();

        $state = $this->getTableFilterState('outlet') ?? [];
        $requestedOutletId = $state['outlet_id'] ?? null;
        $scopeAllOutlets = $requestedOutletId === 'all';

        $selectedOutletId = null;
        if (! $scopeAllOutlets) {
            $candidateOutletId = $requestedOutletId ? (int) $requestedOutletId : (int) $activeOutletId;
            if ($candidateOutletId && in_array($candidateOutletId, $allowedOutletIds, true)) {
                $selectedOutletId = $candidateOutletId;
            } elseif ($activeOutletId && in_array((int) $activeOutletId, $allowedOutletIds, true)) {
                $selectedOutletId = (int) $activeOutletId;
            } else {
                $selectedOutletId = $allowedOutletIds[0] ?? null;
            }
        }

        $outletFilterIds = $scopeAllOutlets
            ? $allowedOutletIds
            : array_values(array_filter([$selectedOutletId]));

        return [$scopeAllOutlets, $outletFilterIds, $selectedOutletId];
    }
}
