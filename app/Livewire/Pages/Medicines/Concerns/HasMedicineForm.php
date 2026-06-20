<?php

namespace App\Livewire\Pages\Medicines\Concerns;

use App\Models\Medicine;
use Filament\Schemas\Schema;
use App\Forms\Schemas\MedicineFormSchema;
use App\DTOs\MedicineData;
use App\Services\MedicineService;

trait HasMedicineForm
{
    public ?Medicine $cMedicine = null;

    public function setMedicine(Medicine $medicine): void
    {
        $this->cMedicine = $medicine;
    }

    public function computeAndSetSku(callable $get, callable $set): void
    {
        $sku = app(MedicineService::class)->generateSku(
            $get('name') ?? '',
            $get('potency') ?? null,
            $get('form') ?? '',
            (int) ($get('packing_quantity') ?? 0),
            $get('packing_unit') ?? ''
        );
        $set('sku', $sku);
    }

    public function saveMedicine(): Medicine
    {
        $data = MedicineData::fromArray($this->form->getState());

        $this->cMedicine = app(MedicineService::class)->save(
            $data,
            $this->cMedicine?->exists ? $this->cMedicine : null,
            auth()->id()
        );

        return $this->cMedicine;
    }

    public function medicineFormSchema(Schema $schema): Schema
    {
        return $schema->components(MedicineFormSchema::schema($this));
    }
}
