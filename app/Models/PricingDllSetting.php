<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingDllSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id',
        'dll_100',
        'dll_200',
        'dll_500',
        'dll_1000',
    ];

    protected $casts = [
        'dll_100' => 'decimal:2',
        'dll_200' => 'decimal:2',
        'dll_500' => 'decimal:2',
        'dll_1000' => 'decimal:2',
    ];
}
