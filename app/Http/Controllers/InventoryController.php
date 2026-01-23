<?php

namespace App\Http\Controllers;

use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\StockService;
use App\Support\AuditLogger;
use App\Support\OutletContext;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(protected StockService $stockService)
    {
    }

    public function index()
    {
        $stocks = InventoryStock::where('outlet_id', OutletContext::id())
            ->with('product', 'variant')
            ->orderBy('id', 'desc')
            ->paginate(20);

        $products = Product::where('outlet_id', OutletContext::id())->orderBy('name')->get();

        return view('inventory.index', compact('stocks', 'products'));
    }

    public function adjust(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'type' => ['required', 'in:in,out,adjust'],
            'qty_grams' => ['required', 'numeric'],
            'reason' => ['nullable', 'string'],
        ]);

        $delta = $data['type'] === 'out' ? -1 * abs($data['qty_grams']) : abs($data['qty_grams']);
        if ($data['type'] === 'adjust') {
            $delta = $data['qty_grams'];
        }

        $stock = $this->stockService->adjust(
            $data['product_id'],
            $data['product_variant_id'] ?? null,
            $delta,
            $data['type'],
            $data['reason'] ?? null
        );

        AuditLogger::log('stock_adjusted', InventoryStock::class, $stock->id, null, $stock->toArray());

        return redirect()->route('inventory.index');
    }
}
