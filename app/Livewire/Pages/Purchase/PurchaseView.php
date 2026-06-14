<?php

namespace App\Livewire\Pages\Purchase;

use App\Models\Purchase;
use Livewire\Component;

class PurchaseView extends Component
{
    public Purchase $purchase;

    public function mount(Purchase $purchase): void
    {
        abort_unless(auth()->user()?->canAccessBranch((int) $purchase->branch_id), 403);

        $this->purchase = $purchase->load([
            'branch',
            'supplier',
            'items.medicine',
            'items.inventoryBatch',
        ]);
    }

    public function render()
    {
        return view('livewire.pages.purchase.purchase-view');
    }
}
