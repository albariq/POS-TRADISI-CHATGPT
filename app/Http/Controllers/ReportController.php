<?php

namespace App\Http\Controllers;

use App\Exports\InventoryReportExport;
use App\Exports\SalesReportExport;
use App\Models\InventoryStock;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Support\OutletContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function sales(Request $request)
    {
        $outletId = OutletContext::id();
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());

        $salesBaseQuery = Sale::where('outlet_id', $outletId)
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->where('status', 'paid');

        $sales = (clone $salesBaseQuery)
            ->with('customer', 'items')
            ->orderByDesc('created_at')
            ->paginate(20);

        $sales->getCollection()->transform(function (Sale $sale) {
            $cogsTotal = $sale->items->sum('cogs_total');
            $netSales = max(0, (float) $sale->subtotal - (float) $sale->discount_total);
            $grossProfit = $netSales - $cogsTotal;
            $grossMargin = $netSales > 0 ? ($grossProfit / $netSales) * 100 : 0;

            $sale->setAttribute('cogs_total', $cogsTotal);
            $sale->setAttribute('net_sales', $netSales);
            $sale->setAttribute('gross_profit', $grossProfit);
            $sale->setAttribute('gross_margin', $grossMargin);

            return $sale;
        });

        $summaryRow = (clone $salesBaseQuery)
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

        $summary = [
            'subtotal' => (float) ($summaryRow->subtotal_sum ?? 0),
            'discount' => (float) ($summaryRow->discount_sum ?? 0),
            'tax' => (float) ($summaryRow->tax_sum ?? 0),
            'service' => (float) ($summaryRow->service_sum ?? 0),
            'grand_total' => (float) ($summaryRow->grand_sum ?? 0),
            'cogs' => (float) $cogsTotal,
        ];
        $summary['net_sales'] = max(0, $summary['subtotal'] - $summary['discount']);
        $summary['gross_profit'] = $summary['net_sales'] - $summary['cogs'];
        $summary['gross_margin'] = $summary['net_sales'] > 0 ? ($summary['gross_profit'] / $summary['net_sales']) * 100 : 0;

        $byProduct = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->where('sales.outlet_id', $outletId)
            ->where('sales.status', 'paid')
            ->whereBetween('sales.created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->groupBy('sale_items.product_id', 'products.name', 'categories.name')
            ->selectRaw('sale_items.product_id as product_id')
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

        $byCategory = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->where('sales.outlet_id', $outletId)
            ->where('sales.status', 'paid')
            ->whereBetween('sales.created_at', [$from.' 00:00:00', $to.' 23:59:59'])
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
                $row->gross_profit = $grossProfit;
                $row->gross_margin = $grossMargin;
                $row->category_name = $row->category_name ?: 'Uncategorized';
                return $row;
            });

        $byCashier = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->leftJoin('users', 'users.id', '=', 'sales.cashier_id')
            ->where('sales.outlet_id', $outletId)
            ->where('sales.status', 'paid')
            ->whereBetween('sales.created_at', [$from.' 00:00:00', $to.' 23:59:59'])
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
                $row->gross_profit = $grossProfit;
                $row->gross_margin = $grossMargin;
                $row->cashier_name = $row->cashier_name ?: 'Unknown';
                return $row;
            });

        return view('reports.sales', compact('sales', 'from', 'to', 'summary', 'byProduct', 'byCategory', 'byCashier'));
    }

    public function inventory()
    {
        $outletId = OutletContext::id();
        $stocks = InventoryStock::where('outlet_id', $outletId)->with('product', 'variant')->paginate(20);
        return view('reports.inventory', compact('stocks'));
    }

    public function exportSalesExcel(Request $request)
    {
        $outletId = OutletContext::id();
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());

        return Excel::download(new SalesReportExport($outletId, $from, $to), 'sales-report.xlsx');
    }

    public function exportInventoryExcel()
    {
        $outletId = OutletContext::id();
        return Excel::download(new InventoryReportExport($outletId), 'inventory-report.xlsx');
    }

    public function exportSalesPdf(Request $request)
    {
        $outletId = OutletContext::id();
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());

        $salesBaseQuery = Sale::where('outlet_id', $outletId)
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->where('status', 'paid');

        $sales = (clone $salesBaseQuery)
            ->with('customer', 'items')
            ->get();

        $sales->transform(function (Sale $sale) {
            $cogsTotal = $sale->items->sum('cogs_total');
            $netSales = max(0, (float) $sale->subtotal - (float) $sale->discount_total);
            $grossProfit = $netSales - $cogsTotal;
            $grossMargin = $netSales > 0 ? ($grossProfit / $netSales) * 100 : 0;

            $sale->setAttribute('cogs_total', $cogsTotal);
            $sale->setAttribute('net_sales', $netSales);
            $sale->setAttribute('gross_profit', $grossProfit);
            $sale->setAttribute('gross_margin', $grossMargin);

            return $sale;
        });

        $summaryRow = (clone $salesBaseQuery)
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

        $summary = [
            'subtotal' => (float) ($summaryRow->subtotal_sum ?? 0),
            'discount' => (float) ($summaryRow->discount_sum ?? 0),
            'tax' => (float) ($summaryRow->tax_sum ?? 0),
            'service' => (float) ($summaryRow->service_sum ?? 0),
            'grand_total' => (float) ($summaryRow->grand_sum ?? 0),
            'cogs' => (float) $cogsTotal,
        ];
        $summary['net_sales'] = max(0, $summary['subtotal'] - $summary['discount']);
        $summary['gross_profit'] = $summary['net_sales'] - $summary['cogs'];
        $summary['gross_margin'] = $summary['net_sales'] > 0 ? ($summary['gross_profit'] / $summary['net_sales']) * 100 : 0;

        $byProduct = SaleItem::query()
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

        $byCategory = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->where('sales.outlet_id', $outletId)
            ->where('sales.status', 'paid')
            ->whereBetween('sales.created_at', [$from.' 00:00:00', $to.' 23:59:59'])
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
                $row->gross_profit = $grossProfit;
                $row->gross_margin = $grossMargin;
                $row->category_name = $row->category_name ?: 'Uncategorized';
                return $row;
            });

        $byCashier = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->leftJoin('users', 'users.id', '=', 'sales.cashier_id')
            ->where('sales.outlet_id', $outletId)
            ->where('sales.status', 'paid')
            ->whereBetween('sales.created_at', [$from.' 00:00:00', $to.' 23:59:59'])
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
                $row->gross_profit = $grossProfit;
                $row->gross_margin = $grossMargin;
                $row->cashier_name = $row->cashier_name ?: 'Unknown';
                return $row;
            });

        $pdf = Pdf::loadView('reports.sales_pdf', compact('sales', 'from', 'to', 'summary', 'byProduct', 'byCategory', 'byCashier'));
        return $pdf->download('sales-report.pdf');
    }

    public function exportInventoryPdf()
    {
        $outletId = OutletContext::id();
        $stocks = InventoryStock::where('outlet_id', $outletId)->with('product', 'variant')->get();
        $pdf = Pdf::loadView('reports.inventory_pdf', compact('stocks'));
        return $pdf->download('inventory-report.pdf');
    }
}
