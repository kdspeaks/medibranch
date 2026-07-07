<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use BelongsToBranch;
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'supplier_id',
        'invoice_number',
        'purchase_date',
        'total_amount',
        'status',
        'notes',
        'ref_code_prefix',
        'ref_code_count',
        'total_mrp',
        'total_discount',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'total_amount'   => 'decimal:2',
            'total_mrp'      => 'decimal:2',
            'total_discount' => 'decimal:2',
        ];
    }

    public function scopeForBranch($query, int $branchId): mixed
    {
        return $query->where('branch_id', $branchId);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
