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
            $data->form,
            $data->packingQuantity,
            $data->packingUnit
        );

        $payload = [
            'name' => $data->name,
            'barcode' => $data->barcode,
            'sku' => $sku,
            'manufacturer_id' => $data->manufacturerId,
            'potency' => $data->potency,
            'form' => $data->form,
            'packing_quantity' => $data->packingQuantity,
            'packing_unit' => $data->packingUnit,
            'purchase_price' => $data->purchasePrice,
            'tax_id' => $data->taxId,
            'is_tax_inclusive' => $data->isTaxInclusive,
            'margin' => $data->margin,
            'sale_price' => $data->salePrice,
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
        string $form,
        int $packingQuantity,
        string $packingUnit
    ): string {
        $potencyStr = $potency ? $potency . '-' : '';
        $formShort = $form ? substr($form, 0, 3) : '';
        $slugName = $name ? strtolower(preg_replace('/[^A-Za-z0-9]/', '_', $name)) : '';
        $unitCode = Medicine::packingUnitCodeMap()[$packingUnit] ?? strtoupper($packingUnit);
        
        $sku = "{$slugName}-{$potencyStr}{$formShort}-{$packingQuantity}{$unitCode}";
        
        return strtoupper(trim($sku, '-'));
    }
}
