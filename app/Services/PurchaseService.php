<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly PricingService $pricingService,
    ) {
    }

    public function save(array $data, ?Purchase $purchase = null): Purchase
    {
        $items = $data['items'] ?? [];
        unset($data['items']);

        $data['status'] = $this->normalizeStatus($data['status'] ?? 'draft');
        $items = $this->normalizeItems($items);
        $data['total_amount'] = $this->pricingService->totalFromItems($items);

        return DB::transaction(function () use ($data, $items, $purchase): Purchase {
            if ($purchase?->exists) {
                return $this->update($purchase, $data, $items);
            }

            return $this->create($data, $items);
        });
    }

    private function create(array $data, array $items): Purchase
    {
        $purchase = Purchase::create($data);
        $this->replaceItems($purchase, $items);

        if ($purchase->status === 'received') {
            $this->receive($purchase);
        }

        return $purchase->fresh(['items.inventoryBatch', 'branch', 'supplier']);
    }

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

    private function replaceItems(Purchase $purchase, array $items): void
    {
        foreach ($items as $item) {
            $purchase->items()->create($item);
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

    private function normalizeItems(array $items): array
    {
        return collect($items)
            ->filter(fn (array $item) => ! empty($item['medicine_id']) && (int) ($item['quantity'] ?? 0) > 0)
            ->map(function (array $item): array {
                $quantity = (int) $item['quantity'];
                $unitPrice = (float) ($item['unit_purchase_price'] ?? 0);
                $taxId = filled($item['tax_id'] ?? null) ? (int) $item['tax_id'] : null;
                $pricing = $this->pricingService->lineWithTax($quantity, $unitPrice, $taxId);

                return [
                    'medicine_id' => (int) $item['medicine_id'],
                    'quantity' => $quantity,
                    'unit_purchase_price' => $this->pricingService->money($unitPrice),
                    'margin' => $this->pricingService->money((float) ($item['margin'] ?? 0)),
                    'batch_number' => filled($item['batch_number'] ?? null) ? $item['batch_number'] : null,
                    'mfg_date' => $item['mfg_date'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'tax_id' => $taxId,
                    'tax_amount' => $pricing['tax_amount'],
                    'line_total_amount' => $pricing['line_total_amount'],
                    'status' => $this->normalizeItemStatus($item),
                ];
            })
            ->values()
            ->all();
    }

    private function normalizeStatus(string $status): string
    {
        return match ($status) {
            'pending' => 'draft',
            'completed' => 'received',
            default => $status,
        };
    }

    private function normalizeItemStatus(array $item): string
    {
        return Arr::get($item, 'status') === 'stocked' ? 'stocked' : 'pending';
    }
}
