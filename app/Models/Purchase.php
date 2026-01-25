<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id',
        'invoice_number',
        'supplier_name',
        'purchased_at',
        'total_cost',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
        'total_cost' => 'decimal:2',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
