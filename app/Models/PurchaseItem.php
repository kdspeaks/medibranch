<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'medicine_id',
        'inventory_batch_id',
        'quantity',
        'unit_purchase_price',
        'mrp',
        'discount_on_purchase',
        'batch_number',
        'mfg_date',
        'expiry_date',
        'tax_id',
        'tax_amount',
        'line_total_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'quantity'             => 'integer',
            'unit_purchase_price'  => 'decimal:2',
            'mrp'                  => 'decimal:2',
            'discount_on_purchase' => 'decimal:2',
            'mfg_date'             => 'date',
            'expiry_date'          => 'date',
            'tax_amount'           => 'decimal:2',
            'line_total_amount'    => 'decimal:2',
        ];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function inventoryBatch(): BelongsTo
    {
        return $this->belongsTo(InventoryBatch::class);
    }
}
