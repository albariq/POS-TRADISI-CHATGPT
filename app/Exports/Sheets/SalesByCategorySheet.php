<?php

namespace App\Exports\Sheets;

use App\Models\SaleItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SalesByCategorySheet implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(
        protected array $outletIds,
        protected string $from,
        protected string $to
    ) {
    }

    public function collection()
    {
        return SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->whereIn('sales.outlet_id', $this->outletIds)
            ->where('sales.status', 'paid')
            ->whereBetween('sales.created_at', [$this->from.' 00:00:00', $this->to.' 23:59:59'])
            ->groupBy('categories.id', 'categories.name')
            ->selectRaw('categories.id as category_id')
            ->selectRaw('categories.name as category_name')
            ->selectRaw('SUM(sale_items.qty) as qty')
            ->selectRaw('SUM(sale_items.line_total) as net_sales')
            ->selectRaw('SUM(sale_items.cogs_total) as cogs_total')
            ->orderByDesc('net_sales')
            ->get()
            ->map(function ($row) {
                $grossProfit = (float) $row->net_sales - (float) $row->cogs_total;
                $grossMargin = (float) $row->net_sales > 0 ? ($grossProfit / (float) $row->net_sales) * 100 : 0;
                return [
                    'category_id' => $row->category_id,
                    'category' => $row->category_name ?: 'Uncategorized',
                    'qty' => (float) $row->qty,
                    'net_sales' => (float) $row->net_sales,
                    'cogs' => (float) $row->cogs_total,
                    'gross_profit' => $grossProfit,
                    'gross_margin_pct' => round($grossMargin, 2),
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Category ID',
            'Category',
            'Qty',
            'Net Sales',
            'COGS',
            'Gross Profit',
            'Gross Margin (%)',
        ];
    }

    public function title(): string
    {
        return 'By Category';
    }
}
