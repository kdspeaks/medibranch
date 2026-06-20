<?php

namespace App\Livewire\Pages\Purchase;

use App\Models\Purchase;
use Livewire\Component;

class PurchaseView extends Component
{
    public Purchase $purchase;

    public function mount(Purchase $purchase): void
    {
        \Illuminate\Support\Facades\Gate::authorize('view', $purchase);

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
