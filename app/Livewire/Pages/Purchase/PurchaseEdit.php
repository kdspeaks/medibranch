<?php

namespace App\Livewire\Pages\Purchase;

use App\Models\Purchase;
use App\Models\Medicine;
use Livewire\Component;
use Filament\Schemas\Schema;
use Filament\Forms\Form;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Actions\Concerns\InteractsWithActions;
use App\Livewire\Pages\Purchase\Concerns\HasPurchaseForm;

class PurchaseEdit extends Component implements HasForms, HasActions
{
    use InteractsWithForms, HasPurchaseForm, InteractsWithActions;

    public ?array $data = [];
    public Purchase $purchaseModel; // the model being edited

    /**
     * Mount with the Purchase model (route-model bound)
     */
    public function mount(Purchase $purchase): void
    {
        $this->purchaseModel = $purchase->load('items');

        // initialize trait-level purchase and fill the form state with model + items
        $this->setPurchase($this->purchaseModel);

        // dd($this->purchaseModel);

        // if the trait's setPurchase already filled the form, keep consistent.
        // Ensure top-level $this->data has model values (form state path is 'data')
        $this->form->fill($this->purchaseModel->load('items')->toArray());
    }

    public function resetForm()
    {
        // Reset form to the original model values
        $this->form->fill($this->purchaseModel->load('items')->toArray());
    }

    /**
     * Update/save the existing purchase
     */
    public function update(): void
    {
        // SavePurchase handles create vs update depending on $this->cPurchase
        $this->savePurchase();

        Notification::make()
            ->title('Purchase updated')
            ->body('Purchase has been successfully updated.')
            ->success()
            ->send();

        // Redirect to a view page — adjust route name as needed
        $this->redirect(route('medicines.purchases.edit', ['purchase' => $this->purchaseModel]), navigate: true);
    }

    /**
     * Generic submit alias (keeps API similar to create)
     */
    public function submit()
    {
        $this->update();
    }

    public function formSubmit()
    {
        $this->submit();
    }

    /**
     * Filament expects: form(Form $form): Form
     * We call the trait's purchaseFormSchema which is Schema-based,
     * then try to extract underlying components for Form::schema(...)
     */
    public function form(Schema $schema): Schema
    {
        // // Build a Schema instance and pass to trait
        // $schema = Schema::make();
        // $schema = $this->purchaseFormSchema($schema);

        // // Try to extract components from Schema.
        // // Defensive: if Schema exposes getComponents(), use it; if it returned array, use that.
        // $components = [];
        // if (is_object($schema) && method_exists($schema, 'getComponents')) {
        //     $components = $schema->getComponents();
        // } elseif (is_array($schema)) {
        //     $components = $schema;
        // } else {
        //     // Fallback: try to cast to array (best-effort)
        //     $components = (array) $schema;
        // }

        // return $form->schema($components)->statePath('data');

         return $this->purchaseFormSchema($schema)
            ->statePath('data');
    }

    public function render()
    {
        return view('livewire.pages.purchase.purchase-edit');
    }

    /**
     * Add item by barcode or name snippet — same behaviour as PurchaseCreate
     */
    public function addByBarcode(string $code)
    {
        $code = trim($code);
        if ($code === '') return;

        $medicine = Medicine::where('barcode', $code)
            ->orWhere('name', 'like', "%{$code}%")
            ->first();

        if ($medicine) {
            $this->addPurchaseItem($medicine->id);

            // clear search UI in child
            $this->dispatch('clear-results');
            return;
        }
    }

    /**
     * Add or bump an item in the current purchase data (identical logic to PurchaseCreate)
     */
    public function addPurchaseItem(int $medicineId): void
    {
        $medicine = Medicine::find($medicineId);
        if (! $medicine) return;

        $items = $this->data['items'] ?? [];

        $existingIndex = collect($items)->search(
            fn($row) => (int)($row['medicine_id'] ?? 0) === (int)$medicine->id
        );

        $ppu = isset($medicine->purchase_price) ? (float) $medicine->purchase_price : 0.0;

        if ($existingIndex !== false) {
            $items[$existingIndex]['quantity'] = (int)($items[$existingIndex]['quantity'] ?? 0) + 1;
            $qty = (int)$items[$existingIndex]['quantity'];

            $unitPrice = isset($items[$existingIndex]['unit_purchase_price'])
                ? (float)$items[$existingIndex]['unit_purchase_price']
                : $ppu;

            $taxId = isset($items[$existingIndex]['tax_id']) ? (int)$items[$existingIndex]['tax_id'] : ($medicine->tax_id ?? null);

            $calc = $this->computeLineWithTax($qty, $unitPrice, $taxId);

            $items[$existingIndex]['unit_purchase_price'] = round($unitPrice, 2);
            $items[$existingIndex]['line_total_amount'] = $calc['line_total_amount'];
            $items[$existingIndex]['tax_amount'] = $calc['tax_amount'];
            $items[$existingIndex]['tax_rate'] = $calc['tax_rate'];
            $items[$existingIndex]['tax_id'] = $taxId;
        } else {
            $qty = 1;
            $unitPrice = $ppu;
            $taxId = $medicine->tax_id ?? null;

            $calc = $this->computeLineWithTax($qty, $unitPrice, $taxId);

            $items[] = [
                'medicine_id'         => $medicine->id,
                'medicine_name'       => $medicine->name,
                'quantity'            => $qty,
                'unit_purchase_price' => round($unitPrice, 2),
                'margin'              => (float)($medicine->margin ?? 0),
                'batch_number'        => '',
                'mfg_date'            => null,
                'expiry_date'         => null,
                'tax_id'              => $taxId,
                'tax_rate'            => $calc['tax_rate'],
                'tax_amount'          => $calc['tax_amount'],
                'line_total_amount'   => $calc['line_total_amount'],
            ];
        }

        $this->data['items'] = array_values($items);

        // recalc total using paise integer math
        $totalInPaise = collect($this->data['items'] ?? [])
            ->reduce(function ($carry, $item) {
                $line = isset($item['line_total_amount']) ? (float) $item['line_total_amount'] : 0.0;
                if (! is_numeric($line)) {
                    return $carry;
                }
                return $carry + (int) round($line * 100);
            }, 0);

        $this->data['total_amount'] = round($totalInPaise / 100, 2);

        // signal to clear search child results if needed
        $this->dispatch('clear-results');
    }
}
