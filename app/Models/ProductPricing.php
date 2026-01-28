<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPricing extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_pricing_extra_id',
        'grams',
        'price',
        'cost',
        'margin',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'margin' => 'decimal:2',
    ];

    public function extra()
    {
        return $this->belongsTo(ProductPricingExtra::class, 'product_pricing_extra_id');
    }
}
