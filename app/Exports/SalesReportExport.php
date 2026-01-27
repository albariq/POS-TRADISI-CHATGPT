<?php

namespace App\Exports;

use App\Exports\Sheets\SalesByCashierSheet;
use App\Exports\Sheets\SalesByCategorySheet;
use App\Exports\Sheets\SalesByProductSheet;
use App\Exports\Sheets\SalesSummarySheet;
use App\Exports\Sheets\SalesTransactionsSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SalesReportExport implements WithMultipleSheets
{
    public function __construct(
        protected array $outletIds,
        protected string $from,
        protected string $to
    ) {
    }

    public function sheets(): array
    {
        return [
            new SalesSummarySheet($this->outletIds, $this->from, $this->to),
            new SalesTransactionsSheet($this->outletIds, $this->from, $this->to),
            new SalesByProductSheet($this->outletIds, $this->from, $this->to),
            new SalesByCategorySheet($this->outletIds, $this->from, $this->to),
            new SalesByCashierSheet($this->outletIds, $this->from, $this->to),
        ];
    }
}
