<?php

namespace App\Models;

use App\Models\Tax;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Medicine extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'barcode',
        'sku', // Unique identifier for scanning
        'manufacturer_id',
        'potency',
        'medicine_form_id',
        'packing_quantity',
        'medicine_unit_id',
        'purchase_price',
        'tax_id',
        'is_tax_inclusive',
        'margin',
        'sale_price',
        'discount_on_sale',
        'description',
        'is_active',
        'created_by',
        'last_updated_by',
    ];

    protected $casts = [
        'packing_quantity' => 'integer',
        'purchase_price' => 'decimal:2',
        'is_tax_inclusive' => 'boolean',
        'margin' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'discount_on_sale' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function medicineForm()
    {
        return $this->belongsTo(MedicineForm::class);
    }

    public function medicineUnit()
    {
        return $this->belongsTo(MedicineUnit::class);
    }
    protected $dates = ['deleted_at']; // Optional: This tells Laravel that `deleted_at` is a date field.

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function getPackingLabelAttribute(): string
    {
        $unitName = $this->medicineUnit ? $this->medicineUnit->name : '';
        return "{$this->packing_quantity} {$unitName}";
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function inventoryBatches()
    {
        return $this->hasManyThrough(
            InventoryBatch::class,
            Inventory::class,
            'medicine_id',
            'inventory_id',
            'id',
            'id',
        );
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
