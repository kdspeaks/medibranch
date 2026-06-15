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
        'form',
        'packing_quantity',
        'packing_unit',
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

    public static function forms(): array
    {
        return [
            'Dilution' => 'Dilution',
            'Mother Tincture' => 'Mother Tincture',
            'Tablet' => 'Tablet',
            'Syrup' => 'Syrup',
            'Trituration' => 'Trituration',
            'Cream' => 'Cream',
            'Ointment' => 'Ointment',
            'Gel' => 'Gel',
            'Drops' => 'Drops',
            'Globules' => 'Globules',
            'Pellets' => 'Pellets',
            'Spray' => 'Spray',
            'Oil' => 'Oil',
        ];
    }

    public static function packingUnits(): array
    {
        return [
            'Dilution' => ['ml'],
            'Mother Tincture' => ['ml'],
            'Tablet' => ['tablets / strip', 'tablets / container'],
            'Trituration' => ['g'],
            'Syrup' => ['ml'],
            'Cream' => ['g'],
            'Ointment' => ['g'],
            'Gel' => ['g'],
            'Drops' => ['ml'],
            'Globules' => ['drams', 'g'],
            'Pellets' => ['drams', 'g'],
            'Spray' => ['ml'],
            'Oil' => ['ml'],
        ];
    }

    public static function packingUnitCodeMap(): array
    {
        return [
            'ml' => 'ML',
            'g' => 'G',
            'drams' => 'DRM',
            'tablets / strip' => '-TAB-STRIP',
            'tablets / container' => '-TAB-CONTR',
        ];
    }


    protected $dates = ['deleted_at']; // Optional: This tells Laravel that `deleted_at` is a date field.

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function getPackingLabelAttribute(): string
    {
        return "{$this->packing_quantity} {$this->packing_unit}";
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
