<?php

namespace App\Livewire\Pages\Medicines;

use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\Medicine;
use App\Models\PurchaseItem;
use App\Services\InventoryService;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Livewire\Component;

class MedicineView extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public Medicine $medicine;

    public ?int $scopedBranchId = null;

    public function mount(Medicine $medicine): void
    {
        $this->medicine = $medicine->load(['manufacturer', 'tax']);
        $this->scopedBranchId = $this->isSuperAdmin() ? null : activeBranch()?->id;
    }

    public function adjustStockAction(): Action
    {
        return Action::make('adjustStock')
            ->label(__('messages.adjust_stock'))
            ->icon('heroicon-o-adjustments-horizontal')
            ->form([
                Radio::make('adjustment_type')
                    ->label(__('messages.adjustment_type'))
                    ->options([
                        'in' => __('messages.stock_in'),
                        'out' => __('messages.stock_out'),
                    ])
                    ->default('in')
                    ->inline()
                    ->live(),

                Select::make('branch_id')
                    ->label(__('messages.branch'))
                    ->options(\App\Models\Branch::pluck('name', 'id'))
                    ->required()
                    ->visible(fn () => $this->isSuperAdmin())
                    ->default(fn () => $this->scopedBranchId()),

                TextInput::make('quantity')
                    ->label(__('messages.quantity'))
                    ->numeric()
                    ->required()
                    ->minValue(1),

                // Fields only for Stock In
                TextInput::make('purchase_price')
                    ->label(__('messages.purchase_price'))
                    ->numeric()
                    ->default($this->medicine->purchase_price)
                    ->required()
                    ->visible(fn ($get) => $get('adjustment_type') === 'in'),

                TextInput::make('mrp')
                    ->label(__('messages.mrp'))
                    ->numeric()
                    ->default($this->medicine->mrp)
                    ->required()
                    ->visible(fn ($get) => $get('adjustment_type') === 'in'),

                TextInput::make('discount_on_purchase')
                    ->label(__('messages.discount_on_purchase'))
                    ->numeric()
                    ->default($this->medicine->discount_on_purchase ?? 0)
                    ->required()
                    ->visible(fn ($get) => $get('adjustment_type') === 'in'),

                TextInput::make('batch_number')
                    ->label(__('messages.batch_number'))
                    ->visible(fn ($get) => $get('adjustment_type') === 'in'),

                DatePicker::make('mfg_date')
                    ->label(__('messages.mfg_date'))
                    ->visible(fn ($get) => $get('adjustment_type') === 'in'),

                DatePicker::make('expiry_date')
                    ->label(__('messages.expiry_date'))
                    ->visible(fn ($get) => $get('adjustment_type') === 'in'),

                // Fields only for Stock Out
                Select::make('preferred_batch_id')
                    ->label(__('messages.preferred_batch'))
                    ->options(function ($get) {
                        $branchId = $get('branch_id') ?? $this->scopedBranchId();
                        if (! $branchId) {
                            return [];
                        }

                        return \App\Models\InventoryBatch::query()
                            ->whereHas('inventory', fn ($q) => $q->where('medicine_id', $this->medicine->id)->where('branch_id', $branchId))
                            ->available()
                            ->get()
                            ->mapWithKeys(fn ($b) => [$b->id => $b->batch_number ? "{$b->batch_number} ({$b->available_quantity} available)" : "No Batch ({$b->available_quantity} available)"]);
                    })
                    ->visible(fn ($get) => $get('adjustment_type') === 'out'),

                TextInput::make('reason')
                    ->label(__('messages.reason'))
                    ->required()
                    ->maxLength(255)
                    ->default(fn ($get) => $get('adjustment_type') === 'in' ? 'Manual Stock Entry' : 'Manual Stock Deduction'),
            ])
            ->action(function (array $data, InventoryService $inventoryService) {
                $branchId = $data['branch_id'] ?? $this->scopedBranchId();

                if (! $branchId) {
                    Notification::make()->title('Branch is required.')->danger()->send();

                    return;
                }

                try {
                    if ($data['adjustment_type'] === 'in') {
                        $inventoryService->stockIn(
                            branchId: $branchId,
                            medicineId: $this->medicine->id,
                            quantity: $data['quantity'],
                            purchasePrice: $data['purchase_price'],
                            mrp: $data['mrp'],
                            discountOnPurchase: $data['discount_on_purchase'],
                            reason: $data['reason'],
                            batchNumber: $data['batch_number'] ?? null,
                            mfgDate: $data['mfg_date'] ?? null,
                            expiryDate: $data['expiry_date'] ?? null,
                            source: null
                        );
                    } else {
                        $inventoryService->stockOut(
                            branchId: $branchId,
                            medicineId: $this->medicine->id,
                            quantity: $data['quantity'],
                            reason: $data['reason'],
                            preferredBatchId: $data['preferred_batch_id'] ?? null,
                            source: null
                        );
                    }

                    Notification::make()
                        ->title(__('messages.stock_adjusted_successfully'))
                        ->success()
                        ->send();

                } catch (Exception $e) {
                    Notification::make()
                        ->title($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
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
                'value' => $summary['expiring_soon'].' batches',
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
