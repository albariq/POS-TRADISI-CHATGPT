<?php

namespace App\Http\Controllers;

use App\Models\InventoryStock;
use App\Models\Sale;
use App\Support\OutletContext;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $outletId = OutletContext::id();

        $todaySales = Sale::where('outlet_id', $outletId)
            ->whereDate('created_at', today())
            ->where('status', 'paid')
            ->sum('grand_total');

        $grossSales = Sale::where('outlet_id', $outletId)
            ->whereDate('created_at', today())
            ->where('status', 'paid')
            ->sum('subtotal');

        $netSales = Sale::where('outlet_id', $outletId)
            ->whereDate('created_at', today())
            ->where('status', 'paid')
            ->sum(DB::raw('grand_total'));

        $driver = DB::connection()->getDriverName();
        $hourExpr = $driver === 'sqlite'
            ? "strftime('%H', created_at)"
            : 'HOUR(created_at)';

        $transactionsByHour = Sale::select(
            DB::raw($hourExpr.' as hour'),
            DB::raw('COUNT(*) as total')
        )
            ->where('outlet_id', $outletId)
            ->whereDate('created_at', today())
            ->where('status', 'paid')
            ->groupBy(DB::raw($hourExpr))
            ->orderBy('hour')
            ->get();

        $topProducts = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->select('sale_items.name_snapshot', DB::raw('SUM(sale_items.qty) as qty'))
            ->where('sales.outlet_id', $outletId)
            ->whereDate('sales.created_at', today())
            ->where('sales.status', 'paid')
            ->groupBy('sale_items.name_snapshot')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        $lowStock = InventoryStock::where('outlet_id', $outletId)
            ->whereColumn('qty_grams', '<=', 'min_qty_grams')
            ->with('product', 'variant')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact('todaySales', 'grossSales', 'netSales', 'transactionsByHour', 'topProducts', 'lowStock'));
    }
}
