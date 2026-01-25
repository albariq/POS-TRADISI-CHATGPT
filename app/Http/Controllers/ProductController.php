<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Tag;
use App\Support\AuditLogger;
use App\Support\OutletContext;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $outletId = OutletContext::id();
        $query = Product::where('outlet_id', $outletId)->with('category');

        if ($request->filled('q')) {
            $query->where('name', 'like', '%'.$request->q.'%')
                ->orWhere('sku', 'like', '%'.$request->q.'%');
        }

        $products = $query->orderBy('name')->paginate(20);

        return view('products.index', compact('products'));
    }

    public function create()
    {
        $outletId = OutletContext::id();
        $categories = Category::where('outlet_id', $outletId)->orderBy('name')->get();
        $tags = Tag::where('outlet_id', $outletId)->orderBy('name')->get();

        return view('products.create', compact('categories', 'tags'));
    }

    public function store(Request $request)
    {
        $outletId = OutletContext::id();

        $data = $request->validate([
            'sku' => ['required', 'string'],
            'name' => ['required', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'base_price' => ['required', 'numeric'],
            'cost_price' => ['nullable', 'numeric'],
            'barcode' => ['nullable', 'string'],
            'has_variants' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'tags' => ['array'],
            'tags.*' => ['integer', 'exists:tags,id'],
            'variants' => ['array'],
            'variants.*.name' => ['nullable', 'string'],
            'variants.*.sku' => ['nullable', 'string'],
            'variants.*.price_override' => ['nullable', 'numeric'],
            'variants.*.cost_price' => ['nullable', 'numeric'],
            'variants.*.grams_per_unit' => ['nullable', 'numeric', 'min:0'],
        ]);

        $product = Product::create([
            'outlet_id' => $outletId,
            'category_id' => $data['category_id'] ?? null,
            'sku' => $data['sku'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'base_price' => $data['base_price'],
            'cost_price' => $data['cost_price'] ?? null,
            'barcode' => $data['barcode'] ?? null,
            'has_variants' => (bool) ($data['has_variants'] ?? false),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        if (! empty($data['tags'])) {
            $product->tags()->sync($data['tags']);
        }

        if (! empty($data['variants'])) {
            foreach ($data['variants'] as $variantData) {
                if (empty($variantData['name'])) {
                    continue;
                }
                ProductVariant::create([
                    'product_id' => $product->id,
                    'name' => $variantData['name'],
                    'sku' => $variantData['sku'] ?? null,
                    'price_override' => $variantData['price_override'] ?? null,
                    'cost_price' => $variantData['cost_price'] ?? null,
                    'grams_per_unit' => $variantData['grams_per_unit'] ?? 0,
                ]);
            }
            $product->update(['has_variants' => true]);
        }

        AuditLogger::log('product_created', Product::class, $product->id, null, $product->toArray());

        return redirect()->route('products.index');
    }

    public function edit(Product $product)
    {
        $this->authorizeOutlet($product->outlet_id);
        $product->load('variants', 'tags');
        $categories = Category::where('outlet_id', $product->outlet_id)->orderBy('name')->get();
        $tags = Tag::where('outlet_id', $product->outlet_id)->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'tags'));
    }

    public function show(Product $product)
    {
        $this->authorizeOutlet($product->outlet_id);

        $product->load('category', 'tags', 'variants');

        $stocks = InventoryStock::where('outlet_id', $product->outlet_id)
            ->where('product_id', $product->id)
            ->with('variant')
            ->orderByRaw('product_variant_id is null desc')
            ->orderBy('product_variant_id')
            ->get();

        $movements = StockMovement::where('outlet_id', $product->outlet_id)
            ->where('product_id', $product->id)
            ->with('variant', 'creator')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('products.show', compact('product', 'stocks', 'movements'));
    }

    public function update(Request $request, Product $product)
    {
        $this->authorizeOutlet($product->outlet_id);

        $data = $request->validate([
            'sku' => ['required', 'string'],
            'name' => ['required', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'base_price' => ['required', 'numeric'],
            'cost_price' => ['nullable', 'numeric'],
            'barcode' => ['nullable', 'string'],
            'has_variants' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'tags' => ['array'],
            'tags.*' => ['integer', 'exists:tags,id'],
            'variants' => ['array'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.name' => ['nullable', 'string'],
            'variants.*.sku' => ['nullable', 'string'],
            'variants.*.price_override' => ['nullable', 'numeric'],
            'variants.*.cost_price' => ['nullable', 'numeric'],
            'variants.*.grams_per_unit' => ['nullable', 'numeric', 'min:0'],
        ]);

        $before = $product->toArray();

        $product->update([
            'sku' => $data['sku'],
            'name' => $data['name'],
            'category_id' => $data['category_id'] ?? null,
            'description' => $data['description'] ?? null,
            'base_price' => $data['base_price'],
            'cost_price' => $data['cost_price'] ?? null,
            'barcode' => $data['barcode'] ?? null,
            'has_variants' => (bool) ($data['has_variants'] ?? false),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        $product->tags()->sync($data['tags'] ?? []);

        if (! empty($data['variants'])) {
            $existingIds = [];
            foreach ($data['variants'] as $variantData) {
                if (empty($variantData['name'])) {
                    continue;
                }
                $variant = null;
                if (! empty($variantData['id'])) {
                    $variant = ProductVariant::where('product_id', $product->id)->find($variantData['id']);
                }
                if ($variant) {
                    $variant->update([
                        'name' => $variantData['name'],
                        'sku' => $variantData['sku'] ?? null,
                        'price_override' => $variantData['price_override'] ?? null,
                        'cost_price' => $variantData['cost_price'] ?? null,
                        'grams_per_unit' => $variantData['grams_per_unit'] ?? 0,
                    ]);
                    $existingIds[] = $variant->id;
                } else {
                    $newVariant = ProductVariant::create([
                        'product_id' => $product->id,
                        'name' => $variantData['name'],
                        'sku' => $variantData['sku'] ?? null,
                        'price_override' => $variantData['price_override'] ?? null,
                        'cost_price' => $variantData['cost_price'] ?? null,
                        'grams_per_unit' => $variantData['grams_per_unit'] ?? 0,
                    ]);
                    $existingIds[] = $newVariant->id;
                }
            }
            ProductVariant::where('product_id', $product->id)
                ->whereNotIn('id', $existingIds)
                ->delete();
        }

        AuditLogger::log('product_updated', Product::class, $product->id, $before, $product->toArray());

        return redirect()->route('products.index');
    }

    protected function authorizeOutlet(int $outletId): void
    {
        if ($outletId !== OutletContext::id()) {
            abort(403);
        }
    }
}
