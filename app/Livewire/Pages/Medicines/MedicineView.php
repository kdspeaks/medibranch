<?php

namespace App\Livewire\Pages\Medicines;

use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\Medicine;
use App\Models\PurchaseItem;
use Livewire\Component;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;

class MedicineView extends Component implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;
    public Medicine $medicine;
    public ?int $scopedBranchId = null;

    public function mount(Medicine $medicine): void
    {
        $this->medicine = $medicine->load(['manufacturer', 'tax']);
        $this->scopedBranchId = $this->isSuperAdmin() ? null : activeBranch()?->id;
    }

    public function render()
    {
        $summary = $this->summary();

        return view('livewire.pages.medicines.medicine-view', [
            'branchLabel' => $this->branchLabel(),
            'summary' => $summary,
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
