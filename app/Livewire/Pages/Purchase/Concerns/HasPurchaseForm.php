<?php

namespace App\Livewire\Pages\Purchase\Concerns;

use App\Models\Purchase;
use App\Models\Branch;
use App\Services\PricingService;
use App\Services\PurchaseService;
use App\Forms\Schemas\PurchaseFormSchema;
use Filament\Schemas\Schema;
use App\DTOs\PurchaseData;

trait HasPurchaseForm
{
    public ?Purchase $cPurchase = null;

    public function setPurchase(Purchase $purchase): void
    {
        $this->cPurchase = $purchase;
        $this->form->fill($purchase->load('items')->toArray());
    }

    public function savePurchase(): Purchase
    {
        $data = PurchaseData::fromArray($this->form->getState());

        $this->cPurchase = app(PurchaseService::class)->save(
            $data,
            $this->cPurchase?->exists ? $this->cPurchase : null
        );

        return $this->cPurchase;
    }

    public function computeLineWithTax(int $qty, float $unitPrice, ?int $taxId)
    {
        return app(PricingService::class)->lineWithTax($qty, $unitPrice, $taxId);
    }

    public function setLinePrices($state, $set, $get)
    {
        $quantity = is_numeric($get('quantity')) ? (float) $get('quantity') : 0.0;
        $unitPurchase = is_numeric($get('unit_purchase_price')) ? (float) $get('unit_purchase_price') : 0.0;
        $taxId = is_numeric($get('tax_id')) ? (int) $get('tax_id') : 0;

        if ($quantity <= 0 || $unitPurchase <= 0) {
            $set('line_total_amount', 0.00);
            $set('tax_amount', 0.00);
            return;
        }

        $price = $this->computeLineWithTax($quantity, $unitPurchase, $taxId);
        $set('line_total_amount', (float) ($price['line_total_amount'] ?? 0.0));
        $set('tax_amount', (float) ($price['tax_amount'] ?? 0.0));
    }

    public function branchOptions(): array
    {
        $user = auth()->user();

        if ($user && ! $user->hasRole('Super Admin')) {
            return $user->branches()->where('is_active', true)->pluck('name', 'branches.id')->toArray();
        }

        return Branch::pluck('name', 'id')->toArray();
    }
    
    public function purchaseFormSchema(Schema $schema): Schema
    {
        return $schema->components(PurchaseFormSchema::schema($this));
    }
}
