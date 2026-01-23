<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesReportExport implements FromCollection, WithHeadings
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
            ->with('customer')
            ->get()
            ->map(function (Sale $sale) {
                return [
                    'receipt_number' => $sale->receipt_number,
                    'date' => $sale->created_at->toDateTimeString(),
                    'customer' => $sale->customer?->name,
                    'subtotal' => $sale->subtotal,
                    'discount' => $sale->discount_total,
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
            'Tax',
            'Service',
            'Grand Total',
        ];
    }
}
