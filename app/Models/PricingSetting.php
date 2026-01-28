<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id',
        'grams',
        'packaging_cost',
        'markup',
    ];

    protected $casts = [
        'markup' => 'decimal:3',
    ];
}
