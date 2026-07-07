<?php

namespace App\Imports;

use App\Models\Medicine;
use App\Models\Manufacturer;
use App\Models\MedicineForm;
use App\Models\MedicineUnit;
use App\Models\Tax;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MedicinesImport implements ToCollection, WithHeadingRow
{
    public $importedCount = 0;
    public $updatedCount = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // The heading row keys are generated from the header row in Excel (usually lowercased and snake_cased)
            // Example headers from our file: "Medicine Name", "Manufacturer", "Barcode", "Form", "Potency", "Packing Quantity", "Unit", "Stored Location", "MRP", "Purchase Discount (%)", "Purchase Price", "Tax (GST %)", "Is Tax Incl. in Price", "Discount on Sale (%)", "Description"

            if (empty($row['medicine_name']) && empty($row['barcode'])) {
                continue; // Skip empty rows
            }

            // 1. Manufacturer
            $manufacturerId = null;
            if (!empty($row['manufacturer'])) {
                $manufacturer = Manufacturer::firstOrCreate(
                    ['name' => trim($row['manufacturer'])],
                    ['is_active' => true]
                );
                $manufacturerId = $manufacturer->id;
            }

            // 2. Form & Unit
            $formId = null;
            $unitId = null;

            if (!empty($row['form'])) {
                $form = MedicineForm::firstOrCreate(
                    ['name' => trim($row['form'])],
                    ['is_active' => true]
                );
                $formId = $form->id;

                if (!empty($row['unit'])) {
                    $unit = MedicineUnit::firstOrCreate(
                        ['name' => trim($row['unit'])],
                        [
                            'short_code' => strtoupper(substr(trim($row['unit']), 0, 3)),
                            'is_active' => true
                        ]
                    );
                    $unitId = $unit->id;

                    // Sync many-to-many relationship
                    if (!$form->units()->where('medicine_unit_id', $unitId)->exists()) {
                        $form->units()->attach($unitId);
                    }
                }
            }

            // 3. Tax
            $taxId = null;
            if (isset($row['tax_gst'])) {
                $rate = floatval($row['tax_gst']);
                $tax = Tax::firstOrCreate(
                    ['rate' => $rate],
                    ['name' => 'GST ' . $rate . '%', 'is_active' => true]
                );
                $taxId = $tax->id;
            }

            // Generate SKU if needed
            $sku = $this->generateSku(trim($row['medicine_name']), trim($row['potency']), trim($row['form']), $row['packing_quantity'], trim($row['unit']));

            // Validate prices
            $mrp = floatval($row['mrp'] ?? 0);
            $discountOnPurchase = floatval($row['purchase_discount'] ?? 0);
            $purchasePrice = floatval($row['purchase_price'] ?? 0);
            
            // If discount is missing but prices exist
            if ($discountOnPurchase <= 0 && $mrp > 0 && $purchasePrice > 0) {
                $discountOnPurchase = (($mrp - $purchasePrice) / $mrp) * 100;
            }

            if ($purchasePrice <= 0 && $mrp > 0) {
                $purchasePrice = $mrp - ($mrp * ($discountOnPurchase / 100));
            }

            // Fallback for barcode
            $barcode = trim($row['barcode'] ?? '');
            if (empty($barcode)) {
                $barcode = 'MB' . rand(1000000000, 9999999999);
            }

            // Check for existing medicine
            $existing = Medicine::where('barcode', $barcode)->first();

            $medicineData = [
                'name' => trim($row['medicine_name']),
                'manufacturer_id' => $manufacturerId,
                'potency' => trim($row['potency'] ?? ''),
                'medicine_form_id' => $formId,
                'packing_quantity' => floatval($row['packing_quantity'] ?? 1),
                'medicine_unit_id' => $unitId,
                'purchase_price' => $purchasePrice,
                'tax_id' => $taxId,
                'is_tax_inclusive' => strtolower(trim($row['is_tax_incl_in_price'] ?? 'yes')) === 'yes',
                'discount_on_purchase' => round($discountOnPurchase, 2),
                'mrp' => $mrp,
                'discount_on_sale' => floatval($row['discount_on_sale'] ?? 0),
                'description' => trim($row['description'] ?? ''),
                'is_active' => true,
            ];

            if ($existing) {
                $existing->update($medicineData);
                $this->updatedCount++;
            } else {
                $medicineData['barcode'] = $barcode;
                
                // Ensure SKU is strictly unique
                $originalSku = $sku;
                $counter = 1;
                while (Medicine::where('sku', $sku)->exists()) {
                    $sku = $originalSku . '-' . $counter;
                    $counter++;
                }
                
                $medicineData['sku'] = $sku;
                Medicine::create($medicineData);
                $this->importedCount++;
            }
        }
    }

    protected function generateSku($name, $potency, $form, $qty, $unit)
    {
        $parts = [];
        if ($name) $parts[] = strtoupper(Str::slug($name, '_'));
        if ($potency) $parts[] = strtoupper(Str::slug($potency));
        if ($form) $parts[] = strtoupper(substr(Str::slug($form), 0, 3));
        if ($qty && $unit) $parts[] = $qty . strtoupper(Str::slug($unit));
        return implode('-', $parts);
    }
}
