<?php

namespace App\DTOs;

readonly class MedicineData
{
    public function __construct(
        public string $name,
        public string $barcode,
        public int $manufacturerId,
        public ?string $potency,
        public int $medicineFormId,
        public int $packingQuantity,
        public int $medicineUnitId,
        public float $purchasePrice,
        public ?int $taxId,
        public bool $isTaxInclusive,
        public float $discountOnPurchase,
        public float $mrp,
        public float $discountOnSale,
        public ?string $description,
        public bool $isActive,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            barcode: $data['barcode'],
            manufacturerId: (int) $data['manufacturer_id'],
            potency: $data['potency'] ?? null,
            medicineFormId: (int) $data['medicine_form_id'],
            packingQuantity: (int) $data['packing_quantity'],
            medicineUnitId: (int) $data['medicine_unit_id'],
            purchasePrice: (float) ($data['purchase_price'] ?? 0),
            taxId: filled($data['tax_id'] ?? null) ? (int) $data['tax_id'] : null,
            isTaxInclusive: (bool) ($data['is_tax_inclusive'] ?? true),
            discountOnPurchase: (float) ($data['discount_on_purchase'] ?? 0),
            mrp: (float) ($data['mrp'] ?? 0),
            discountOnSale: (float) ($data['discount_on_sale'] ?? 0),
            description: $data['description'] ?? null,
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }
}
