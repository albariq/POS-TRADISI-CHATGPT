<?php

namespace App\Http\Controllers;

use App\Exports\InventoryReportExport;
use App\Exports\SalesReportExport;
use App\Models\InventoryStock;
use App\Models\Sale;
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

        $sales = Sale::where('outlet_id', $outletId)
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->where('status', 'paid')
            ->with('customer')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('reports.sales', compact('sales', 'from', 'to'));
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

        $sales = Sale::where('outlet_id', $outletId)
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->where('status', 'paid')
            ->with('customer')
            ->get();

        $pdf = Pdf::loadView('reports.sales_pdf', compact('sales', 'from', 'to'));
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
