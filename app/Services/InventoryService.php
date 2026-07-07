<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryBatch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryService
{
    public function __construct(
        private readonly PricingService $pricingService
    ) {}
    public function stockIn(
        int $branchId,
        int $medicineId,
        int $quantity,
        float $purchasePrice,
        float $mrp,
        float $discountOnPurchase,
        ?string $reason = null,
        ?string $batchNumber = null,
        ?string $mfgDate = null,
        ?string $expiryDate = null,
        ?Model $source = null,
    ): InventoryBatch {
        $inventory = Inventory::firstOrCreate([
            'branch_id' => $branchId,
            'medicine_id' => $medicineId,
        ]);

        $batchQuery = $inventory->batches();

        $batchNumber === null
            ? $batchQuery->whereNull('batch_number')
            : $batchQuery->where('batch_number', $batchNumber);

        $batch = $batchQuery->first();

        if ($batch) {
            $batch->increment('quantity', $quantity);
            $batch->increment('available_quantity', $quantity);
            $batch->update([
                'unit_purchase_price' => $purchasePrice,
                'mrp' => $mrp,
                'discount_on_purchase' => $discountOnPurchase,
                'mfg_date' => $mfgDate,
                'expiry_date' => $expiryDate,
                'status' => 'active',
            ]);
        } else {
            $batch = $inventory->batches()->create([
                'quantity' => $quantity,
                'available_quantity' => $quantity,
                'unit_purchase_price' => $purchasePrice,
                'mrp' => $mrp,
                'discount_on_purchase' => $discountOnPurchase,
                'batch_number' => $batchNumber,
                'mfg_date' => $mfgDate,
                'expiry_date' => $expiryDate,
                'status' => 'active',
            ]);
        }

        $this->log($batch, 'in', $quantity, $reason, $source);

        $medicine = \App\Models\Medicine::find($medicineId);
        if ($medicine) {
            // Update medicine prices if they are zero, or if the new purchase price is different
            $medicine->update([
                'purchase_price' => $purchasePrice,
                'discount_on_purchase' => $discountOnPurchase,
                'mrp' => $mrp,
            ]);
        }

        return $batch->fresh(['inventory', 'logs']);
    }

    public function stockOut(
        int $branchId,
        int $medicineId,
        int $quantity,
        ?string $reason = null,
        ?int $preferredBatchId = null,
        ?Model $source = null,
    ): ?Inventory {
        return DB::transaction(function () use (
            $branchId,
            $medicineId,
            $quantity,
            $reason,
            $preferredBatchId,
            $source
        ) {
            $inventory = Inventory::query()
                ->forBranch($branchId)
                ->where('medicine_id', $medicineId)
                ->lockForUpdate()
                ->first();

            if (! $inventory) {
                return null;
            }

            if ($inventory->quantity < $quantity) {
                throw new RuntimeException('Insufficient stock in inventory.');
            }

            $remaining = $quantity;

            $batches = $this->deductionBatches($inventory, $preferredBatchId);

            foreach ($batches as $batch) {
                if ($remaining <= 0) {
                    break;
                }

                $deductQuantity = min($batch->available_quantity, $remaining);
                $batch->decrement('available_quantity', $deductQuantity);
                $this->log($batch, 'out', $deductQuantity, $reason, $source);

                $remaining -= $deductQuantity;
            }

            if ($remaining > 0) {
                throw new RuntimeException('Insufficient stock in selected batch.');
            }

            return $inventory->fresh();
        });
    }

    private function deductionBatches(Inventory $inventory, ?int $preferredBatchId)
    {
        $query = $inventory->batches()
            ->available()
            ->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END, expiry_date ASC')
            ->orderBy('created_at', 'ASC')
            ->lockForUpdate();

        if ($preferredBatchId) {
            $query->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$preferredBatchId]);
        }

        return $query->get();
    }

    private function log(
        InventoryBatch $batch,
        string $type,
        int $quantity,
        ?string $reason,
        ?Model $source,
    ): void {
        $batch->logs()->create([
            'type' => $type,
            'quantity' => $quantity,
            'reason' => $reason,
            'source_type' => $source?->getMorphClass(),
            'source_id' => $source?->getKey(),
        ]);
    }
}
