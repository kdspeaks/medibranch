<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryBatch;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class InventoryService
{
    public function stockIn(
        int $branchId,
        int $medicineId,
        int $quantity,
        float $purchasePrice,
        float $margin,
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
                'margin' => $margin,
                'mfg_date' => $mfgDate,
                'expiry_date' => $expiryDate,
                'status' => 'active',
            ]);
        } else {
            $batch = $inventory->batches()->create([
                'quantity' => $quantity,
                'available_quantity' => $quantity,
                'unit_purchase_price' => $purchasePrice,
                'margin' => $margin,
                'batch_number' => $batchNumber,
                'mfg_date' => $mfgDate,
                'expiry_date' => $expiryDate,
                'status' => 'active',
            ]);
        }

        $this->log($batch, 'in', $quantity, $reason, $source);

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
        $inventory = Inventory::query()
            ->forBranch($branchId)
            ->where('medicine_id', $medicineId)
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
    }

    private function deductionBatches(Inventory $inventory, ?int $preferredBatchId)
    {
        $query = $inventory->batches()
            ->available()
            ->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END, expiry_date ASC')
            ->orderBy('created_at', 'ASC');

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
