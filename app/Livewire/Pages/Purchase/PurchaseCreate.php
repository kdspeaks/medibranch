<?php

namespace App\Livewire\Pages\Purchase;

use Livewire\Component;
use App\Models\Medicine;
// use Filament\Actions\Action;
use Filament\Forms\Form;
use Livewire\Attributes\On;
use Filament\Forms\Components\Grid;
use Illuminate\Contracts\View\View;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Livewire\Pages\Purchase\Concerns\HasPurchaseForm;

class PurchaseCreate extends Component implements HasForms
{

    use InteractsWithForms, HasPurchaseForm;
    public ?array $data = [];

    public function mount(): void
    {
        $this->cPurchase = new \App\Models\Purchase();
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

        return $this->redirect(route('medicines.list'), navigate: true); // Update this route to your actual list page
    }

    public function submitAndCreate()
    {
        $this->submit();

        $this->form->fill(); // Reset the form for creating another medicine
        $this->dispatch('scroll-to-top');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->purchaseFormSchema())
            ->statePath('data');
    }

    public function render()
    {
        return view('livewire.pages.purchase.purchase-create');
    }

    // PurchaseCreate.php

    public function addPurchaseItem(int $medicineId): void
    {
        $medicine = \App\Models\Medicine::find($medicineId);
        if (! $medicine) return;

        // get existing items
        $items = $this->data['items'] ?? [];

        // if already present, just bump quantity & totals
        $existingIndex = collect($items)->search(
            fn($row) => (int)($row['medicine_id'] ?? 0) === (int)$medicine->id
        );

        if ($existingIndex !== false) {
            $items[$existingIndex]['quantity'] = (int)($items[$existingIndex]['quantity'] ?? 0) + 1;
            $qty = (int)$items[$existingIndex]['quantity'];
            $ppu = (float)($items[$existingIndex]['unit_purchase_price'] ?? 0);
            $items[$existingIndex]['total_amount'] = $qty * $ppu;
        } else {
            $ppu = (float)($medicine->purchase_price ?? 0);
            $items[] = [
                'medicine_id'         => $medicine->id,
                'medicine_name'       => $medicine->name,
                'quantity'            => 1,
                'unit_purchase_price' => $ppu,
                'unit_selling_price'  => (float)($medicine->selling_price ?? 0),
                'batch_number'        => '',
                'mfg_date'            => null,
                'expiry_date'          => null,
                'tax_id'              => null,
                'total_amount'        => 1 * $ppu,
            ];
        }

        // reindex to keep repeater happy
        $this->data['items'] = array_values($items);

        // optional: recompute purchase total
        $this->data['total_amount'] = collect($this->data['items'])
            ->sum(fn($r) => (float)($r['total_amount'] ?? 0));
    }
}
