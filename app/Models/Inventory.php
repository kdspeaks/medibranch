<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = [
        'branch_id',
        'medicine_id'
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

    public function getQuantityAttribute()
    {
        return (int)$this->batches()->sum('available_quantity');
    }

    public static function stockIn(
        int $branchId,
        int $medicineId,
        int $qty,
        float $purchasePrice,
        float $sellingPrice,
        ?string $reason = null,
        ?string $batchNumber = null,
        ?string $expiryDate = null
    ): Inventory {
        // 1️⃣ Find or create inventory record for branch + medicine
        $inventory = self::firstOrCreate(
            [
                'branch_id'   => $branchId,
                'medicine_id' => $medicineId,
            ],
            [
                'quantity' => 0,
            ]
        );

        // 2️⃣ Find batch
        $batchQuery = $inventory->batches()->where('batch_number', $batchNumber);

        if ($batchNumber === null) {
            $batchQuery->whereNull('batch_number');
        }

        $batch = $batchQuery->first();

        if ($batch) {
            // Update existing batch
            $batch->increment('quantity', $qty);
            $batch->increment('available_quantity', $qty);

            // Optionally update prices / expiry
            $batch->update([
                'unit_purchase_price' => $purchasePrice,
                'unit_selling_price'  => $sellingPrice,
                'expiry_date'    => $expiryDate,
            ]);
        } else {
            // Create new batch
            $batch = $inventory->batches()->create([
                'quantity'           => $qty,
                'available_quantity' => $qty,
                'unit_purchase_price'     => $purchasePrice,
                'unit_selling_price'      => $sellingPrice,
                'batch_number'       => $batchNumber,
                'expiry_date'        => $expiryDate,
                'status'             => 'active',
            ]);
        }

        // 3️⃣ Create log
        $batch->logs()->create([
            'type'     => 'in',
            'quantity' => $qty,
            'reason'   => $reason,
        ]);

        // 4️⃣ Update inventory quantity
        // $inventory->increment('quantity', $qty);

        // 5️⃣ Return inventory with all batches + logs
        return $inventory->fresh(['batches.logs']);
    }

    public static function stockOut(
        int $branchId,
        int $medicineId,
        int $qty,
        ?string $reason = null
    ): Inventory {
        // 1️⃣ Get inventory record
        $inventory = self::where('branch_id', $branchId)
            ->where('medicine_id', $medicineId)
            ->with('batches')
            ->first();

        if (! $inventory) {
            throw new \Exception("No inventory found for this branch and medicine.");
        }

        if ($inventory->quantity < $qty) {
            throw new \Exception("Insufficient stock in inventory.");
        }

        $remainingQty = $qty;

        // 2️⃣ Get batches in FIFO order (by expiry_date if set, else by creation date)
        $batches = $inventory->batches()
            ->where('available_quantity', '>', 0)
            ->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END, expiry_date ASC')
            ->orderBy('created_at', 'ASC')
            ->get();

        foreach ($batches as $batch) {
            if ($remainingQty <= 0) {
                break;
            }

            $deductQty = min($batch->available_quantity, $remainingQty);

            // Deduct from batch
            $batch->decrement('available_quantity', $deductQty);

            // Log the stock out
            $batch->logs()->create([
                'type'     => 'out',
                'quantity' => $deductQty,
                'reason'   => $reason,
            ]);

            $remainingQty -= $deductQty;
        }

        // 3️⃣ Deduct from main inventory total
        // $inventory->decrement('quantity', $qty);

        // 4️⃣ Return fresh inventory with all batches + logs
        return $inventory->fresh([
            'batches' => fn($query) => $query->where('available_quantity', '>', 0)
        ]);
    }
}
