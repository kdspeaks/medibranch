<?php

namespace App\Services;

use App\DTOs\MedicineData;
use App\Models\Medicine;

class MedicineService
{
    public function save(MedicineData $data, ?Medicine $medicine = null, ?int $userId = null): Medicine
    {
        $sku = $this->generateSku(
            $data->name,
            $data->potency,
            $data->medicineFormId,
            $data->packingQuantity,
            $data->medicineUnitId,
            $data->manufacturerId
        );

        $payload = [
            'name' => $data->name,
            'barcode' => $data->barcode,
            'sku' => $sku,
            'manufacturer_id' => $data->manufacturerId,
            'potency' => $data->potency,
            'medicine_form_id' => $data->medicineFormId,
            'packing_quantity' => $data->packingQuantity,
            'medicine_unit_id' => $data->medicineUnitId,
            'purchase_price' => $data->purchasePrice,
            'tax_id' => $data->taxId,
            'is_tax_inclusive' => $data->isTaxInclusive,
            'discount_on_purchase' => $data->discountOnPurchase,
            'mrp' => $data->mrp,
            'discount_on_sale' => $data->discountOnSale,
            'description' => $data->description,
            'is_active' => $data->isActive,
        ];

        if ($medicine && $medicine->exists) {
            $payload['last_updated_by'] = $userId;
            $medicine->update($payload);
            return $medicine;
        }

        $payload['created_by'] = $userId;
        return Medicine::create($payload);
    }

    public function generateSku(
        string $name,
        ?string $potency,
        int $medicineFormId,
        int $packingQuantity,
        int $medicineUnitId,
        ?int $manufacturerId = null
    ): string {
        $potencyStr = $potency ? $potency . '-' : '';
        
        $form = \App\Models\MedicineForm::find($medicineFormId);
        $unit = \App\Models\MedicineUnit::find($medicineUnitId);
        $brand = $manufacturerId ? \App\Models\Manufacturer::find($manufacturerId) : null;
        
        $formName = $form ? $form->name : '';
        $formShort = $formName ? substr($formName, 0, 3) : '';
        $slugName = $name ? strtolower(preg_replace('/[^A-Za-z0-9]/', '_', $name)) : '';
        $unitCode = $unit && $unit->short_code ? $unit->short_code : ($unit ? strtoupper($unit->name) : '');
        $brandStr = $brand ? $this->generateBrandCode($brand->name) . '-' : '';
        
        $sku = "{$brandStr}{$slugName}-{$potencyStr}{$formShort}-{$packingQuantity}{$unitCode}";
        
        return strtoupper(trim($sku, '-'));
    }

    protected function generateBrandCode(string $brandName): string
    {
        $cleanName = preg_replace('/[^A-Za-z0-9\s]/', '', $brandName);
        $words = array_values(array_filter(explode(' ', $cleanName)));
        
        if (count($words) > 1) {
            $code = '';
            foreach ($words as $word) {
                $code .= strtoupper(substr($word, 0, 1));
            }
            return substr($code, 0, 4);
        }
        
        return strtoupper(substr($cleanName, 0, 3));
    }
}
