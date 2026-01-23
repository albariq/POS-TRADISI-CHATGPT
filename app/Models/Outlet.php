<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Outlet extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'phone',
        'address',
        'tax_rate',
        'service_charge_rate',
        'rounding_unit',
        'currency_code',
        'timezone',
        'is_active',
    ];

    protected $casts = [
        'tax_rate' => 'decimal:2',
        'service_charge_rate' => 'decimal:2',
        'rounding_unit' => 'integer',
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('is_default')->withTimestamps();
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
