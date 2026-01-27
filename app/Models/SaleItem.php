<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'product_variant_id',
        'name_snapshot',
        'sku_snapshot',
        'qty',
        'grams_per_unit',
        'grams_total',
        'unit_price',
        'cost_price_snapshot',
        'cogs_total',
        'discount_amount',
        'tax_amount',
        'line_total',
        'note',
    ];

    protected $casts = [
        'qty' => 'integer',
        'grams_per_unit' => 'decimal:2',
        'grams_total' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'cost_price_snapshot' => 'decimal:2',
        'cogs_total' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
