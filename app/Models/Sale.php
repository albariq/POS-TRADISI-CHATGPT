<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id',
        'receipt_number',
        'status',
        'subtotal',
        'discount_total',
        'tax_total',
        'service_total',
        'rounding_adjustment',
        'grand_total',
        'tax_rate',
        'service_charge_rate',
        'customer_id',
        'cashier_id',
        'shift_id',
        'notes',
        'paid_at',
        'voided_at',
        'void_reason',
        'public_token',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'service_total' => 'decimal:2',
        'rounding_adjustment' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'service_charge_rate' => 'decimal:2',
        'paid_at' => 'datetime',
        'voided_at' => 'datetime',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
