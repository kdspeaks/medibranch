<?php

namespace App\Livewire\Pages\Medicines;

use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\Medicine;
use App\Models\PurchaseItem;
use Livewire\Component;

class MedicineView extends Component
{
    public Medicine $medicine;

    public function mount(Medicine $medicine): void
    {
        $this->medicine = $medicine->load(['manufacturer', 'tax']);
    }

    public function render()
    {
        $summary = $this->summary();

        return view('livewire.pages.medicines.medicine-view', [
            'branchLabel' => $this->branchLabel(),
            'summary' => $summary,
            'branchStocks' => $this->branchStocks(),
            'batches' => $this->batches(),
            'movements' => $this->movements(),
            'purchases' => $this->purchases(),
            'alerts' => $this->alerts($summary),
        ]);
    }

    private function isSuperAdmin(): bool
    {
        return auth()->user()?->hasRole('Super Admin') ?? false;
    }

    private function scopedBranchId(): ?int
    {
        if ($this->isSuperAdmin()) {
            return null;
        }

        return activeBranch()?->id;
    }

    private function branchLabel(): string
    {
        if ($this->isSuperAdmin()) {
            return 'All branches';
        }

        return activeBranch()?->name ?? 'Active branch';
    }

    private function inventoryQuery()
    {
        return Inventory::query()
            ->where('medicine_id', $this->medicine->id)
            ->when($branchId = $this->scopedBranchId(), fn ($query) => $query->forBranch($branchId));
    }

    private function batchQuery()
    {
        return $this->medicine->inventoryBatches()
            ->with('inventory.branch')
            ->when($branchId = $this->scopedBranchId(), fn ($query) => $query->whereHas('inventory', fn ($inventory) => $inventory->forBranch($branchId)))
            ->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END, expiry_date ASC')
            ->orderBy('created_at', 'DESC');
    }

    private function movementQuery()
    {
        return InventoryLog::query()
            ->whereHas('batch.inventory', function ($query) {
                $query->where('medicine_id', $this->medicine->id)
                    ->when($branchId = $this->scopedBranchId(), fn ($inventory) => $inventory->forBranch($branchId));
            })
            ->with(['batch.inventory.branch', 'source'])
            ->latest();
    }

    private function purchaseQuery()
    {
        return PurchaseItem::query()
            ->where('medicine_id', $this->medicine->id)
            ->with(['purchase.branch', 'purchase.supplier', 'inventoryBatch'])
            ->when($branchId = $this->scopedBranchId(), fn ($query) => $query->whereHas('purchase', fn ($purchase) => $purchase->forBranch($branchId)))
            ->latest();
    }

    private function summary(): array
    {
        $batches = $this->batchQuery()->get();
        $totalStock = (int) $batches->sum('available_quantity');
        $expiringSoon = $batches->filter(function ($batch) {
            return $batch->expiry_date
                && $batch->expiry_date->between(now(), now()->addDays(90));
        })->count();

        return [
            'total_stock' => $totalStock,
            'batch_count' => $batches->count(),
            'inventory_count' => $this->inventoryQuery()->count(),
            'purchase_count' => $this->purchaseQuery()->count(),
            'movement_count' => $this->movementQuery()->count(),
            'expiring_soon' => $expiringSoon,
            'stock_state' => $totalStock === 0 ? 'Out of stock' : ($totalStock <= 10 ? 'Low stock' : 'In stock'),
        ];
    }

    private function branchStocks()
    {
        return $this->inventoryQuery()
            ->with(['branch', 'batches' => fn ($query) => $query->available()])
            ->get()
            ->map(function (Inventory $inventory) {
                return [
                    'branch_name' => $inventory->branch?->name ?? '-',
                    'branch_code' => $inventory->branch?->code ?? '-',
                    'quantity' => $inventory->quantity,
                    'batch_count' => $inventory->batches->count(),
                    'available_batches' => $inventory->batches->count(),
                ];
            });
    }

    private function batches()
    {
        return $this->batchQuery()
            ->get()
            ->map(function ($batch) {
                return [
                    'branch_name' => $batch->inventory?->branch?->name ?? '-',
                    'batch_number' => $batch->batch_number ?: '-',
                    'quantity' => (int) $batch->quantity,
                    'available_quantity' => (int) $batch->available_quantity,
                    'unit_purchase_price' => (float) $batch->unit_purchase_price,
                    'margin' => (float) $batch->margin,
                    'mfg_date' => $batch->mfg_date?->format('d M, Y') ?? '-',
                    'expiry_date' => $batch->expiry_date?->format('d M, Y') ?? '-',
                    'status' => $batch->status,
                    'is_expiring_soon' => $batch->expiry_date?->between(now(), now()->addDays(90)) ?? false,
                ];
            });
    }

    private function movements()
    {
        $movements = $this->movementQuery()->get();
        $movements->loadMorph('source', [
            PurchaseItem::class => ['purchase.branch', 'purchase.supplier'],
        ]);

        return $movements
            ->map(function (InventoryLog $log) {
                $sourceLabel = 'System';

                if ($log->source) {
                    $sourceLabel = class_basename($log->source);

                    if ($log->source instanceof PurchaseItem) {
                        $sourceLabel = trim('Purchase #' . ($log->source->purchase?->ref_code_prefix ?? '') . ($log->source->purchase?->ref_code_count ?? ''));
                    }
                }

                return [
                    'created_at' => $log->created_at?->format('d M, Y H:i') ?? '-',
                    'branch_name' => $log->batch?->inventory?->branch?->name ?? '-',
                    'batch_number' => $log->batch?->batch_number ?: '-',
                    'type' => $log->type,
                    'quantity' => (int) $log->quantity,
                    'reason' => $log->reason ?: '-',
                    'source_label' => $sourceLabel ?: 'System',
                ];
            });
    }

    private function purchases()
    {
        return $this->purchaseQuery()
            ->get()
            ->map(function (PurchaseItem $item) {
                return [
                    'purchase_ref' => trim(($item->purchase?->ref_code_prefix ?? '') . ($item->purchase?->ref_code_count ?? '')),
                    'purchase_date' => $item->purchase?->purchase_date?->format('d M, Y') ?? '-',
                    'branch_name' => $item->purchase?->branch?->name ?? '-',
                    'supplier_name' => $item->purchase?->supplier?->name ?? 'Walk-in',
                    'batch_number' => $item->batch_number ?: '-',
                    'quantity' => (int) $item->quantity,
                    'unit_price' => (float) $item->unit_purchase_price,
                    'line_total_amount' => (float) $item->line_total_amount,
                    'status' => $item->status,
                ];
            });
    }

    private function alerts(array $summary): array
    {
        return [
            [
                'label' => 'Stock state',
                'value' => $summary['stock_state'],
                'tone' => $summary['stock_state'] === 'In stock' ? 'success' : ($summary['stock_state'] === 'Low stock' ? 'warning' : 'danger'),
            ],
            [
                'label' => 'Expiring soon',
                'value' => $summary['expiring_soon'] . ' batches',
                'tone' => $summary['expiring_soon'] > 0 ? 'warning' : 'success',
            ],
            [
                'label' => 'Active scope',
                'value' => $this->branchLabel(),
                'tone' => 'neutral',
            ],
        ];
    }
}
