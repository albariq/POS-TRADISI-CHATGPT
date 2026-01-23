<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id',
        'product_id',
        'product_variant_id',
        'type',
        'qty',
        'before_qty',
        'after_qty',
        'qty_grams',
        'before_qty_grams',
        'after_qty_grams',
        'reason',
        'reference_type',
        'reference_id',
        'created_by',
    ];

    protected $casts = [
        'qty' => 'integer',
        'before_qty' => 'integer',
        'after_qty' => 'integer',
        'qty_grams' => 'decimal:2',
        'before_qty_grams' => 'decimal:2',
        'after_qty_grams' => 'decimal:2',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
