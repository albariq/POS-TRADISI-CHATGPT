<?php

namespace App\Exports\Sheets;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SalesTransactionsSheet implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(
        protected int $outletId,
        protected string $from,
        protected string $to
    ) {
    }

    public function collection()
    {
        return Sale::where('outlet_id', $this->outletId)
            ->whereBetween('created_at', [$this->from.' 00:00:00', $this->to.' 23:59:59'])
            ->where('status', 'paid')
            ->with('customer', 'items')
            ->get()
            ->map(function (Sale $sale) {
                $cogsTotal = $sale->items->sum('cogs_total');
                $netSales = max(0, (float) $sale->subtotal - (float) $sale->discount_total);
                $grossProfit = $netSales - $cogsTotal;
                $grossMargin = $netSales > 0 ? ($grossProfit / $netSales) * 100 : 0;

                return [
                    'receipt_number' => $sale->receipt_number,
                    'date' => $sale->created_at->toDateTimeString(),
                    'customer' => $sale->customer?->name,
                    'subtotal' => $sale->subtotal,
                    'discount' => $sale->discount_total,
                    'net_sales' => $netSales,
                    'cogs' => $cogsTotal,
                    'gross_profit' => $grossProfit,
                    'gross_margin_pct' => round($grossMargin, 2),
                    'tax' => $sale->tax_total,
                    'service' => $sale->service_total,
                    'grand_total' => $sale->grand_total,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Receipt',
            'Date',
            'Customer',
            'Subtotal',
            'Discount',
            'Net Sales',
            'COGS',
            'Gross Profit',
            'Gross Margin (%)',
            'Tax',
            'Service',
            'Grand Total',
        ];
    }

    public function title(): string
    {
        return 'Transactions';
    }
}
