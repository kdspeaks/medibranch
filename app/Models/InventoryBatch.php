<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryBatch extends Model
{
    protected $fillable = [
        'inventory_id',
        'quantity',
        'available_quantity',
        'unit_purchase_price',
        'margin',
        'batch_number',
        'mfg_date',
        'expiry_date',
        'status',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'available_quantity' => 'integer',
        'unit_purchase_price' => 'decimal:2',
        'margin' => 'decimal:2',
        'mfg_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function logs()
    {
        return $this->hasMany(InventoryLog::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('available_quantity', '>', 0);
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($query) {
            $query->whereNull('expiry_date')
                ->orWhere('expiry_date', '>=', now()->toDateString());
        });
    }
}
