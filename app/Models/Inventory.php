<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use BelongsToBranch;
    use SoftDeletes;

    protected $fillable = [
        'branch_id',
        'medicine_id',
        'stored_location',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(InventoryBatch::class);
    }

    public function scopeForBranch($query, int $branchId): mixed
    {
        return $query->where('branch_id', $branchId);
    }

    public function getQuantityAttribute(): int
    {
        return (int) $this->batches()->sum('available_quantity');
    }
}
