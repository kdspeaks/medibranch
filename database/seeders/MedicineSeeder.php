<?php

namespace Database\Seeders;

use App\Models\Manufacturer;
use App\Models\Medicine;
use App\Models\Tax;
use Illuminate\Database\Seeder;

class MedicineSeeder extends Seeder
{
    public function run(): void
    {
        $manufacturerId = Manufacturer::query()->value('id');
        $taxId = Tax::query()->where('rate', 5)->value('id') ?? Tax::query()->value('id');

        $medicines = [
            [
                'name' => 'Arsenicum Album 30CH',
                'barcode' => 'MB1000000001',
                'sku' => 'ARSENICUM_ALBUM-30CH-DIL-30ML',
                'manufacturer_id' => $manufacturerId,
                'potency' => '30CH',
                'form' => 'Dilution',
                'packing_quantity' => 30,
                'packing_unit' => 'ml',
                'purchase_price' => 82.24,
                'tax_id' => $taxId,
                'is_tax_inclusive' => true,
                'mrp' => 100.30,
                'discount_on_purchase' => 18,
                'discount_on_sale' => 0,
                'description' => 'Commonly stocked dilution bottle.',
                'is_active' => true,
            ],
            [
                'name' => 'Nux Vomica 200CH',
                'barcode' => 'MB1000000002',
                'sku' => 'NUX_VOMICA-200CH-DIL-30ML',
                'manufacturer_id' => $manufacturerId,
                'potency' => '200CH',
                'form' => 'Dilution',
                'packing_quantity' => 30,
                'packing_unit' => 'ml',
                'purchase_price' => 91.20,
                'tax_id' => $taxId,
                'is_tax_inclusive' => true,
                'mrp' => 114,
                'discount_on_purchase' => 20,
                'discount_on_sale' => 0,
                'description' => 'Fast-moving dilution item.',
                'is_active' => true,
            ],
            [
                'name' => 'Bryonia Alba Q',
                'barcode' => 'MB1000000003',
                'sku' => 'BRYONIA_ALBA-Q-MOT-30ML',
                'manufacturer_id' => $manufacturerId,
                'potency' => 'Q',
                'form' => 'Mother Tincture',
                'packing_quantity' => 30,
                'packing_unit' => 'ml',
                'purchase_price' => 66.61,
                'tax_id' => $taxId,
                'is_tax_inclusive' => true,
                'mrp' => 85.40,
                'discount_on_purchase' => 22,
                'discount_on_sale' => 0,
                'description' => 'Mother tincture stock item.',
                'is_active' => true,
            ],
            [
                'name' => 'Calendula Ointment',
                'barcode' => 'MB1000000004',
                'sku' => 'CALENDULA-OIN-25G',
                'manufacturer_id' => $manufacturerId,
                'potency' => null,
                'form' => 'Ointment',
                'packing_quantity' => 25,
                'packing_unit' => 'g',
                'purchase_price' => 56.25,
                'tax_id' => $taxId,
                'is_tax_inclusive' => true,
                'mrp' => 75,
                'discount_on_purchase' => 25,
                'discount_on_sale' => 0,
                'description' => 'Topical ointment stock item.',
                'is_active' => true,
            ],
            [
                'name' => 'Biochemic Tablet Mix',
                'barcode' => 'MB1000000005',
                'sku' => 'BIOCHEMIC-TAB-25TAB-STRIP',
                'manufacturer_id' => $manufacturerId,
                'potency' => null,
                'form' => 'Tablet',
                'packing_quantity' => 25,
                'packing_unit' => 'tablets / strip',
                'purchase_price' => 31.85,
                'tax_id' => $taxId,
                'is_tax_inclusive' => true,
                'mrp' => 45.50,
                'discount_on_purchase' => 30,
                'discount_on_sale' => 0,
                'description' => 'Tablet strip for counter sales.',
                'is_active' => true,
            ],
        ];

        foreach ($medicines as $medicine) {
            $formName = $medicine['form'];
            $unitName = $medicine['packing_unit'];
            
            unset($medicine['form'], $medicine['packing_unit']);
            
            $form = \App\Models\MedicineForm::firstOrCreate(['name' => $formName]);
            $unit = \App\Models\MedicineUnit::firstOrCreate(['name' => $unitName]);
            
            // Sync pivot table
            if (!$form->units()->where('medicine_unit_id', $unit->id)->exists()) {
                $form->units()->attach($unit->id);
            }
            
            $medicine['medicine_form_id'] = $form->id;
            $medicine['medicine_unit_id'] = $unit->id;

            Medicine::updateOrCreate(
                ['barcode' => $medicine['barcode']],
                $medicine,
            );
        }

        Medicine::factory(15)->create();
    }
}
