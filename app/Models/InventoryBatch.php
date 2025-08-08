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
        'unit_selling_price',
        'batch_number',
        'expiry_date',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function logs()
    {
        return $this->hasMany(InventoryLog::class);
    }
}

