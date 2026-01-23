<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id',
        'earn_rate_amount',
        'earn_rate_points',
        'redeem_rate_amount',
    ];

    protected $casts = [
        'earn_rate_amount' => 'integer',
        'earn_rate_points' => 'integer',
        'redeem_rate_amount' => 'integer',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }
}
