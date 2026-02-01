<?php

namespace App\Exports;

use App\Models\Sale;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesDetailExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(protected Builder $query)
    {
    }

    public function query()
    {
        return (clone $this->query)
            ->with(['outlet', 'cashier', 'customer', 'payments', 'items'])
            ->withSum('items as cogs_total', 'cogs_total');
    }

    public function headings(): array
    {
        return [
            'Receipt',
            'Status',
            'Outlet',
            'Cashier',
            'Customer',
            'Created At',
            'Paid At',
            'Subtotal',
            'Discount',
            'Net Sales',
            'COGS',
            'Gross Profit',
            'Gross Margin (%)',
            'Tax',
            'Service',
            'Rounding',
            'Grand Total',
            'Payment Methods',
            'Notes',
            'Void Reason',
            'Voided At',
        ];
    }

    /**
     * @param  Sale  $sale
     */
    public function map($sale): array
    {
        $netSales = max(0, (float) $sale->subtotal - (float) $sale->discount_total);
        $cogsTotal = (float) ($sale->cogs_total ?? $sale->items->sum('cogs_total'));
        $grossProfit = $netSales - $cogsTotal;
        $grossMargin = $netSales > 0 ? ($grossProfit / $netSales) * 100 : 0;

        $paymentSummary = $sale->payments
            ->map(fn ($payment) => $this->formatPaymentMethod($payment->method).':'.(float) $payment->amount)
            ->implode(' | ');

        return [
            $sale->receipt_number,
            $this->formatStatus($sale->status),
            $sale->outlet?->name,
            $sale->cashier?->name,
            $sale->customer?->name,
            $sale->created_at?->toDateTimeString(),
            $sale->paid_at?->toDateTimeString(),
            (float) $sale->subtotal,
            (float) $sale->discount_total,
            $netSales,
            $cogsTotal,
            $grossProfit,
            round($grossMargin, 2),
            (float) $sale->tax_total,
            (float) $sale->service_total,
            (float) $sale->rounding_adjustment,
            (float) $sale->grand_total,
            $paymentSummary,
            $sale->notes,
            $sale->void_reason,
            $sale->voided_at?->toDateTimeString(),
        ];
    }

    private function formatStatus(?string $status): string
    {
        return match ($status) {
            'paid' => 'Dibayar',
            'draft' => 'Draft',
            'void' => 'Void',
            'refunded' => 'Refund',
            default => $status ?? '-',
        };
    }

    private function formatPaymentMethod(?string $method): string
    {
        return match ($method) {
            'cash' => 'Tunai',
            'card' => 'Kartu',
            'qris' => 'QRIS',
            'ewallet' => 'E-Wallet',
            'transfer' => 'Transfer',
            default => $method ?? '-',
        };
    }
}
