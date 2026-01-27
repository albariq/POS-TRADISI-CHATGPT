<?php

namespace App\Exports\Sheets;

use App\Models\SaleItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SalesByCashierSheet implements FromCollection, WithHeadings, WithTitle
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
            ->leftJoin('users', 'users.id', '=', 'sales.cashier_id')
            ->whereIn('sales.outlet_id', $this->outletIds)
            ->where('sales.status', 'paid')
            ->whereBetween('sales.created_at', [$this->from.' 00:00:00', $this->to.' 23:59:59'])
            ->groupBy('sales.cashier_id', 'users.name')
            ->selectRaw('sales.cashier_id as cashier_id')
            ->selectRaw('users.name as cashier_name')
            ->selectRaw('COUNT(DISTINCT sales.id) as transactions')
            ->selectRaw('SUM(sale_items.line_total) as net_sales')
            ->selectRaw('SUM(sale_items.cogs_total) as cogs_total')
            ->orderByDesc('net_sales')
            ->get()
            ->map(function ($row) {
                $grossProfit = (float) $row->net_sales - (float) $row->cogs_total;
                $grossMargin = (float) $row->net_sales > 0 ? ($grossProfit / (float) $row->net_sales) * 100 : 0;
                return [
                    'cashier_id' => $row->cashier_id,
                    'cashier' => $row->cashier_name ?: 'Unknown',
                    'transactions' => (int) $row->transactions,
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
            'Cashier ID',
            'Cashier',
            'Transactions',
            'Net Sales',
            'COGS',
            'Gross Profit',
            'Gross Margin (%)',
        ];
    }

    public function title(): string
    {
        return 'By Cashier';
    }
}
