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

    protected $casts = [
        'quantity' => 'integer',
        'unit_purchase_price' => 'decimal:2',
        'margin' => 'decimal:2',
        'mfg_date' => 'date',
        'expiry_date' => 'date',
        'tax_amount' => 'decimal:2',
        'line_total_amount' => 'decimal:2',
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
