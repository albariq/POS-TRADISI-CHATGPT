<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id',
        'name',
        'email',
        'phone',
        'address',
        'points_balance',
        'is_active',
    ];

    protected $casts = [
        'points_balance' => 'integer',
        'is_active' => 'boolean',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
