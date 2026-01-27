<?php

namespace App\Exports\Sheets;

use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SalesSummarySheet implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(
        protected int $outletId,
        protected string $from,
        protected string $to
    ) {
    }

    public function collection(): Collection
    {
        $summaryRow = Sale::where('outlet_id', $this->outletId)
            ->whereBetween('created_at', [$this->from.' 00:00:00', $this->to.' 23:59:59'])
            ->where('status', 'paid')
            ->selectRaw('SUM(subtotal) as subtotal_sum')
            ->selectRaw('SUM(discount_total) as discount_sum')
            ->selectRaw('SUM(tax_total) as tax_sum')
            ->selectRaw('SUM(service_total) as service_sum')
            ->selectRaw('SUM(grand_total) as grand_sum')
            ->first();

        $cogsTotal = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.outlet_id', $this->outletId)
            ->where('sales.status', 'paid')
            ->whereBetween('sales.created_at', [$this->from.' 00:00:00', $this->to.' 23:59:59'])
            ->sum('sale_items.cogs_total');

        $subtotal = (float) ($summaryRow->subtotal_sum ?? 0);
        $discount = (float) ($summaryRow->discount_sum ?? 0);
        $netSales = max(0, $subtotal - $discount);
        $grossProfit = $netSales - (float) $cogsTotal;
        $grossMargin = $netSales > 0 ? ($grossProfit / $netSales) * 100 : 0;

        return collect([[
            'subtotal' => $subtotal,
            'discount' => $discount,
            'net_sales' => $netSales,
            'cogs' => (float) $cogsTotal,
            'gross_profit' => $grossProfit,
            'gross_margin_pct' => round($grossMargin, 2),
            'tax' => (float) ($summaryRow->tax_sum ?? 0),
            'service' => (float) ($summaryRow->service_sum ?? 0),
            'grand_total' => (float) ($summaryRow->grand_sum ?? 0),
        ]]);
    }

    public function headings(): array
    {
        return [
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
        return 'Summary';
    }
}
