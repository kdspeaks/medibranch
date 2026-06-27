<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'margin' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'sub_total' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
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
