<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPricingExtra extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'modal_1kg',
        'modal_1gr',
        'modal_100',
        'modal_200',
        'modal_500',
        'dll_100',
        'dll_200',
        'dll_500',
        'dll_1000',
    ];

    protected $casts = [
        'modal_1kg' => 'decimal:2',
        'modal_1gr' => 'decimal:2',
        'modal_100' => 'decimal:2',
        'modal_200' => 'decimal:2',
        'modal_500' => 'decimal:2',
        'dll_100' => 'decimal:2',
        'dll_200' => 'decimal:2',
        'dll_500' => 'decimal:2',
        'dll_1000' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function pricings()
    {
        return $this->hasMany(ProductPricing::class);
    }
}
