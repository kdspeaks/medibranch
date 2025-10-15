<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'medicine_id',
        'inventory_batch_id',
        'quantity',
        'unit_purchase_price',
        'margin',
        'batch_number',
        'mfg_date',
        'expiry_date',
        'tax_id',
        'tax_amount',
        'line_total_amount',
        'status',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function inventoryBatch()
    {
        return $this->belongsTo(InventoryBatch::class);
    }
}
