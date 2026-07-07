<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medicine extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'barcode',
        'sku',
        'manufacturer_id',
        'potency',
        'medicine_form_id',
        'packing_quantity',
        'medicine_unit_id',
        'purchase_price',
        'tax_id',
        'is_tax_inclusive',
        'discount_on_purchase',
        'mrp',
        'discount_on_sale',
        'description',
        'is_active',
        'created_by',
        'last_updated_by',
    ];

    protected function casts(): array
    {
        return [
            'packing_quantity'      => 'integer',
            'purchase_price'        => 'decimal:2',
            'is_tax_inclusive'      => 'boolean',
            'discount_on_purchase'  => 'decimal:2',
            'mrp'                   => 'decimal:2',
            'discount_on_sale'      => 'decimal:2',
            'is_active'             => 'boolean',
        ];
    }

    public function scopeActive($query): mixed
    {
        return $query->where('is_active', true);
    }

    public function medicineForm(): BelongsTo
    {
        return $this->belongsTo(MedicineForm::class);
    }

    public function medicineUnit(): BelongsTo
    {
        return $this->belongsTo(MedicineUnit::class);
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function inventoryBatches(): HasManyThrough
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

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function getPackingLabelAttribute(): string
    {
        $unitName = $this->medicineUnit ? $this->medicineUnit->name : '';

        return "{$this->packing_quantity} {$unitName}";
    }
}
