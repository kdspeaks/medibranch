<?php

namespace App\DTOs;

readonly class MedicineData
{
    public function __construct(
        public string $name,
        public string $barcode,
        public int $manufacturerId,
        public ?string $potency,
        public string $form,
        public int $packingQuantity,
        public string $packingUnit,
        public float $purchasePrice,
        public ?int $taxId,
        public bool $isTaxInclusive,
        public float $margin,
        public float $salePrice,
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
            form: $data['form'],
            packingQuantity: (int) $data['packing_quantity'],
            packingUnit: $data['packing_unit'],
            purchasePrice: (float) ($data['purchase_price'] ?? 0),
            taxId: filled($data['tax_id'] ?? null) ? (int) $data['tax_id'] : null,
            isTaxInclusive: (bool) ($data['is_tax_inclusive'] ?? true),
            margin: (float) ($data['margin'] ?? 0),
            salePrice: (float) ($data['sale_price'] ?? 0),
            discountOnSale: (float) ($data['discount_on_sale'] ?? 0),
            description: $data['description'] ?? null,
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }
}
