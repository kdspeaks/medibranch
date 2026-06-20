<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\DTOs\PurchaseData;
use App\DTOs\PurchaseItemData;

class PurchaseService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly PricingService $pricingService,
    ) {
    }

    public function save(PurchaseData $data, ?Purchase $purchase = null): Purchase
    {
        $totalAmount = $this->pricingService->totalFromItems(array_map(function (PurchaseItemData $item) {
            return [
                'quantity' => $item->quantity,
                'unit_purchase_price' => $item->unitPurchasePrice,
                'tax_id' => $item->taxId,
            ];
        }, $data->items));

        $purchaseDataArray = [
            'branch_id' => $data->branchId,
            'supplier_id' => $data->supplierId,
            'invoice_number' => $data->invoiceNumber,
            'purchase_date' => $data->purchaseDate,
            'status' => $data->status,
            'notes' => $data->notes,
            'ref_code_prefix' => $data->refCodePrefix,
            'ref_code_count' => $data->refCodeCount,
            'total_amount' => $totalAmount,
        ];

        return DB::transaction(function () use ($purchaseDataArray, $data, $purchase): Purchase {
            if ($purchase?->exists) {
                return $this->update($purchase, $purchaseDataArray, $data->items);
            }

            return $this->create($purchaseDataArray, $data->items);
        });
    }

    /**
     * @param PurchaseItemData[] $items
     */
    private function create(array $data, array $items): Purchase
    {
        $purchase = Purchase::create($data);
        $this->replaceItems($purchase, $items);

        if ($purchase->status === 'received') {
            $this->receive($purchase);
        }

        return $purchase->fresh(['items.inventoryBatch', 'branch', 'supplier']);
    }

    /**
     * @param PurchaseItemData[] $items
     */
    private function update(Purchase $purchase, array $data, array $items): Purchase
    {
        if ($purchase->status === 'received' || $purchase->items()->where('status', 'stocked')->exists()) {
            throw ValidationException::withMessages([
                'status' => 'Received purchases cannot be edited safely after stock has been added.',
            ]);
        }

        $purchase->update($data);
        $purchase->items()->delete();
        $this->replaceItems($purchase, $items);

        if ($purchase->status === 'received') {
            $this->receive($purchase);
        }

        return $purchase->fresh(['items.inventoryBatch', 'branch', 'supplier']);
    }

    /**
     * @param PurchaseItemData[] $items
     */
    private function replaceItems(Purchase $purchase, array $items): void
    {
        foreach ($items as $item) {
            $pricing = $this->pricingService->lineWithTax($item->quantity, $item->unitPurchasePrice, $item->taxId);
            
            $purchase->items()->create([
                'medicine_id' => $item->medicineId,
                'quantity' => $item->quantity,
                'unit_purchase_price' => $item->unitPurchasePrice,
                'margin' => $item->margin,
                'batch_number' => $item->batchNumber,
                'mfg_date' => $item->mfgDate,
                'expiry_date' => $item->expiryDate,
                'tax_id' => $item->taxId,
                'tax_amount' => $pricing['tax_amount'],
                'line_total_amount' => $pricing['line_total_amount'],
                'status' => $item->status,
            ]);
        }
    }

    private function receive(Purchase $purchase): void
    {
        $purchase->loadMissing('items');

        foreach ($purchase->items as $item) {
            if ($item->status === 'stocked') {
                continue;
            }

            $batch = $this->inventoryService->stockIn(
                branchId: (int) $purchase->branch_id,
                medicineId: (int) $item->medicine_id,
                quantity: (int) $item->quantity,
                purchasePrice: (float) $item->unit_purchase_price,
                margin: (float) $item->margin,
                reason: 'purchase_received',
                batchNumber: $item->batch_number,
                mfgDate: $item->mfg_date?->toDateString(),
                expiryDate: $item->expiry_date?->toDateString(),
                source: $item,
            );

            $item->update([
                'inventory_batch_id' => $batch->id,
                'status' => 'stocked',
            ]);
        }
    }


}
