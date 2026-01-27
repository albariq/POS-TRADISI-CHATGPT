<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'sku',
        'name',
        'description',
        'base_price',
        'cost_price',
        'barcode',
        'has_variants',
        'is_active',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'has_variants' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function outlets()
    {
        return $this->belongsToMany(Outlet::class, 'product_outlets')->withTimestamps();
    }

    public function scopeForOutlet($query, ?int $outletId)
    {
        if (! $outletId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('outlets', fn ($q) => $q->where('outlets.id', $outletId));
    }

    public function isAvailableInOutlet(int $outletId): bool
    {
        return $this->outlets()->where('outlets.id', $outletId)->exists();
    }
}
