<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryLog extends Model
{
    protected $fillable = [
        'inventory_batch_id',
        'type',
        'quantity',
        'reason',
        'source_type',
        'source_id',
    ];

    public function batch()
    {
        return $this->belongsTo(InventoryBatch::class, 'inventory_batch_id');
    }

    public function source()
    {
        return $this->morphTo();
    }
}
