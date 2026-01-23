<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\SaleService;
use App\Services\SalesCalculator;
use App\Support\OutletContext;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function __construct(
        protected SalesCalculator $calculator,
        protected SaleService $saleService
    ) {
    }

    public function index(Request $request)
    {
        $outletId = OutletContext::id();
        $query = Product::where('outlet_id', $outletId)->where('is_active', true)->with('variants');
        if ($request->filled('q')) {
            $query->where('name', 'like', '%'.$request->q.'%')
                ->orWhere('sku', 'like', '%'.$request->q.'%')
                ->orWhere('barcode', 'like', '%'.$request->q.'%');
        }

        $products = $query->orderBy('name')->limit(30)->get();
        $customers = Customer::where('outlet_id', $outletId)->orderBy('name')->limit(20)->get();

        $cart = $this->getCart();
        $outlet = OutletContext::outlet();
        $coupon = $cart['coupon_id'] ? Coupon::find($cart['coupon_id']) : null;
        $cart['coupon_discount'] = $this->saleService->calculateCouponDiscount(
            $coupon,
            $cart['items'],
            $cart['transaction_discount'],
            $outlet
        );
        $totals = $this->calculator->calculate($cart['items'], $outlet, $cart['transaction_discount'], $cart['coupon_discount']);

        return view('pos.index', compact('products', 'customers', 'cart', 'totals'));
    }

    public function addItem(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'qty' => ['nullable', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        if ($product->outlet_id !== OutletContext::id()) {
            abort(403);
        }

        $variant = null;
        if (! empty($data['product_variant_id'])) {
            $variant = ProductVariant::findOrFail($data['product_variant_id']);
            if ($variant->product_id !== $product->id) {
                abort(422);
            }
        }

        $gramsPerUnit = (float) ($variant?->grams_per_unit ?? 0);
        if ($gramsPerUnit <= 0) {
            abort(422, 'Variant grams per unit is required.');
        }

        $cart = $this->getCart();
        $key = $product->id.':'.($variant?->id ?? 'base');
        $qty = $data['qty'] ?? 1;
        $price = $variant?->price_override ?? $product->base_price;

        if (isset($cart['items'][$key])) {
            $cart['items'][$key]['qty'] += $qty;
        } else {
            $cart['items'][$key] = [
                'key' => $key,
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'name' => $product->name.($variant ? ' - '.$variant->name : ''),
                'sku' => $variant?->sku ?? $product->sku,
                'qty' => $qty,
                'grams_per_unit' => $gramsPerUnit,
                'unit_price' => (float) $price,
                'discount_amount' => 0,
                'note' => null,
            ];
        }

        $this->storeCart($cart);

        return redirect()->route('pos.index');
    }

    public function updateItem(Request $request)
    {
        $data = $request->validate([
            'key' => ['required', 'string'],
            'qty' => ['required', 'integer', 'min:1'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
        ]);

        $cart = $this->getCart();
        if (! isset($cart['items'][$data['key']])) {
            return redirect()->route('pos.index');
        }

        $cart['items'][$data['key']]['qty'] = $data['qty'];
        $cart['items'][$data['key']]['discount_amount'] = $data['discount_amount'] ?? 0;
        $cart['items'][$data['key']]['note'] = $data['note'] ?? null;

        $this->storeCart($cart);

        return redirect()->route('pos.index');
    }

    public function removeItem(Request $request)
    {
        $data = $request->validate([
            'key' => ['required', 'string'],
        ]);

        $cart = $this->getCart();
        unset($cart['items'][$data['key']]);
        $this->storeCart($cart);

        return redirect()->route('pos.index');
    }

    public function applyDiscount(Request $request)
    {
        $data = $request->validate([
            'transaction_discount' => ['required', 'numeric', 'min:0'],
        ]);

        $cart = $this->getCart();
        $cart['transaction_discount'] = $data['transaction_discount'];
        $this->storeCart($cart);

        return redirect()->route('pos.index');
    }

    public function applyCoupon(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string'],
        ]);

        $coupon = Coupon::where('code', $data['code'])
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('outlet_id')
                    ->orWhere('outlet_id', OutletContext::id());
            })
            ->first();

        $cart = $this->getCart();
        if ($coupon) {
            $cart['coupon_id'] = $coupon->id;
            $cart['coupon_discount'] = 0;
        }
        $this->storeCart($cart);

        return redirect()->route('pos.index');
    }

    public function hold(Request $request)
    {
        $cart = $this->getCart();
        $outlet = OutletContext::outlet();
        $coupon = $cart['coupon_id'] ? Coupon::find($cart['coupon_id']) : null;

        $this->saleService->createDraft(
            $outlet,
            array_values($cart['items']),
            $cart['customer_id'],
            $cart['transaction_discount'],
            $coupon
        );

        $this->clearCart();

        return redirect()->route('pos.index');
    }

    public function checkout(Request $request)
    {
        $data = $request->validate([
            'payment_method' => ['required', 'in:cash,card,qris,ewallet,transfer'],
            'payment_amount' => ['required', 'numeric', 'min:0'],
            'cash_received' => ['nullable', 'numeric', 'min:0'],
            'payment_reference' => ['nullable', 'string'],
            'customer_id' => ['nullable', 'exists:customers,id'],
        ]);

        $cart = $this->getCart();
        $cart['customer_id'] = $data['customer_id'] ?? $cart['customer_id'];
        $outlet = OutletContext::outlet();
        $coupon = $cart['coupon_id'] ? Coupon::find($cart['coupon_id']) : null;

        $totals = $this->calculator->calculate($cart['items'], $outlet, $cart['transaction_discount'], $cart['coupon_discount']);
        $change = 0;
        if ($data['payment_method'] === 'cash') {
            $received = $data['cash_received'] ?? $data['payment_amount'];
            $change = max(0, $received - $totals['grand_total']);
        }

        $sale = $this->saleService->checkout(
            $outlet,
            array_values($cart['items']),
            [[
                'method' => $data['payment_method'],
                'amount' => $data['payment_amount'],
                'reference' => $data['payment_reference'] ?? null,
                'change_amount' => $change,
            ]],
            $cart['customer_id'],
            $cart['transaction_discount'],
            $coupon
        );

        $this->clearCart();

        return redirect()->route('receipts.show', $sale->id);
    }

    protected function getCart(): array
    {
        return session()->get('cart', [
            'items' => [],
            'transaction_discount' => 0,
            'coupon_id' => null,
            'coupon_discount' => 0,
            'customer_id' => null,
        ]);
    }

    protected function storeCart(array $cart): void
    {
        session()->put('cart', $cart);
    }

    protected function clearCart(): void
    {
        session()->forget('cart');
    }
}
