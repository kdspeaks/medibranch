<?php

namespace App\Models;

use App\Services\InventoryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use SoftDeletes;
    use \App\Models\Concerns\BelongsToBranch;


    protected $fillable = [
        'branch_id',
        'medicine_id',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function batches()
    {
        return $this->hasMany(InventoryBatch::class);
    }

    public function getQuantityAttribute(): int
    {
        return (int) $this->batches()->sum('available_quantity');
    }

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public static function stockIn(
        int $branchId,
        int $medicineId,
        int $qty,
        float $purchasePrice,
        float $margin,
        ?string $reason = null,
        ?string $batchNumber = null,
        ?string $mfgDate = null,
        ?string $expiryDate = null
    ): Inventory {
        return app(InventoryService::class)->stockIn(
            branchId: $branchId,
            medicineId: $medicineId,
            quantity: $qty,
            purchasePrice: $purchasePrice,
            margin: $margin,
            reason: $reason,
            batchNumber: $batchNumber,
            mfgDate: $mfgDate,
            expiryDate: $expiryDate,
        )->inventory->fresh(['batches.logs']);
    }

    public static function stockOut(
        int $branchId,
        int $medicineId,
        int $qty,
        ?string $reason = null
    ): ?Inventory {
        return app(InventoryService::class)->stockOut(
            branchId: $branchId,
            medicineId: $medicineId,
            quantity: $qty,
            reason: $reason,
        )?->fresh([
            'batches' => fn ($query) => $query->where('available_quantity', '>', 0),
        ]);
    }
}
