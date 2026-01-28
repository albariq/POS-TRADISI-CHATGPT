<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingPercentageSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id',
        'pct_100',
        'pct_200',
        'pct_500',
        'pct_1000',
    ];

    protected $casts = [
        'pct_100' => 'decimal:3',
        'pct_200' => 'decimal:3',
        'pct_500' => 'decimal:3',
        'pct_1000' => 'decimal:3',
    ];
}
