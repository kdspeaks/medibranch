<?php

namespace App\Livewire\Pages\Purchase;

use App\Models\Purchase;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Schema;
use Livewire\Component;
use App\Models\Medicine;
use Livewire\Attributes\On;
use Filament\Forms\Components\Grid;
use Illuminate\Contracts\View\View;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Livewire\Pages\Purchase\Concerns\HasPurchaseForm;
use Filament\Actions\Concerns\InteractsWithActions;

class PurchaseCreate extends Component implements HasForms, HasActions
{

    use InteractsWithForms, HasPurchaseForm, InteractsWithActions;
    public ?array $data = [];

    public function mount(): void
    {
        $this->cPurchase = new Purchase();
        $this->form->fill();
    }

    public function resetForm()
    {
        $this->form->fill();
    }



    public function submit()
    {
        $this->savePurchase();

        Notification::make()
            ->title('Medicine Created')
            ->body('Medicines have been successfully created.')
            ->success()
            ->send();
    }
    public function formSubmit()
    {
        $this->submit();

        // return $this->redirect(route('purchases.create'), navigate: true); // Update this route to your actual list page
    }

    public function submitAndCreate()
    {
        $this->submit();

        $this->form->fill(); // Reset the form for creating another medicine
        $this->dispatch('scroll-to-top');
    }

    public function form(Schema $schema): Schema
    {
        return $this->purchaseFormSchema($schema)
            ->statePath('data');
    }

    public function render()
    {
        return view('livewire.pages.purchase.purchase-create');
    }

    // PurchaseCreate.php

    public function addByBarcode(string $code)
    {
        $code = trim($code);
        if ($code === '') return;
        // dd($code);
        // Try lookup by barcode(s)
        $medicine = Medicine::where('barcode', $code)
            ->orWhere('name', 'like', "%{$code}%")
            ->first();

        if ($medicine) {
            // call your existing parent-level method
            $this->addPurchaseItem($medicine->id);


            // optional: reset any search state in child by dispatching back or clearing relevant props
            // e.g., you may want to reset query on parent so child sees it via binding
            // $this->dispatchBrowserEvent('toast', ['message' => 'Added: ' . $medicine->name]);
            return;
        }
    }

    public function addPurchaseItem(int $medicineId): void
    {
        $medicine = Medicine::find($medicineId);
        if (! $medicine) return;

        // get existing items
        $items = $this->data['items'] ?? [];

        // if already present, just bump quantity & totals
        $existingIndex = collect($items)->search(
            fn($row) => (int)($row['medicine_id'] ?? 0) === (int)$medicine->id
        );

        // determine base unit purchase price (float)
        $ppu = isset($medicine->purchase_price) ? (float) $medicine->purchase_price : 0.0;

        
        if ($existingIndex !== false) {
            // bump qty
            $items[$existingIndex]['quantity'] = (int)($items[$existingIndex]['quantity'] ?? 0) + 1;
            $qty = (int)$items[$existingIndex]['quantity'];

            // prefer existing unit_purchase_price if present, otherwise fallback to $ppu
            $unitPrice = isset($items[$existingIndex]['unit_purchase_price'])
                ? (float)$items[$existingIndex]['unit_purchase_price']
                : $ppu;

            $taxId = isset($items[$existingIndex]['tax_id']) ? (int)$items[$existingIndex]['tax_id'] : ($medicine->tax_id ?? null);

            $calc = $this->computeLineWithTax($qty, $unitPrice, $taxId);

            // store computed values back on the item
            $items[$existingIndex]['unit_purchase_price'] = round($unitPrice, 2);
            $items[$existingIndex]['line_total_amount'] = $calc['line_total_amount'];
            $items[$existingIndex]['tax_amount'] = $calc['tax_amount'];
            $items[$existingIndex]['tax_rate'] = $calc['tax_rate'];
            $items[$existingIndex]['tax_id'] = $taxId;
        } else {
            // new item
            $qty = 1;
            $unitPrice = $ppu;
            $taxId = $medicine->tax_id ?? null;

            $calc = $this->computeLineWithTax($qty, $unitPrice, $taxId);

            $items[] = [
                'medicine_id'         => $medicine->id,
                'medicine_name'       => $medicine->name,
                'quantity'            => $qty,
                'unit_purchase_price' => round($unitPrice, 2),
                'margin'  => (float)($medicine->margin ?? 0),
                'batch_number'        => '',
                'mfg_date'            => null,
                'expiry_date'         => null,
                'tax_id'              => $taxId,
                'tax_rate'            => $calc['tax_rate'],
                'tax_amount'          => $calc['tax_amount'],
                'line_total_amount'   => $calc['line_total_amount'],
            ];
        }

        // reindex to keep repeater happy
        $this->data['items'] = array_values($items);

        // safe summation using integer paise to avoid float drift (sum item->line_total)
        $totalInPaise = collect($this->data['items'] ?? [])
            ->reduce(function ($carry, $item) {
                $line = isset($item['line_total_amount']) ? (float) $item['line_total_amount'] : 0.0;

                if (! is_numeric($line)) {
                    return $carry;
                }

                return $carry + (int) round($line * 100); // convert to paise
            }, 0);

        // convert back to decimal with two places
        $this->data['total_amount'] = round($totalInPaise / 100, 2);

        // optional: dispatch to clear child search
        $this->dispatch('clear-results');
    }
}
