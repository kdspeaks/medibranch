<?php

namespace App\DTOs;

readonly class PurchaseItemData
{
    public function __construct(
        public int $medicineId,
        public int $quantity,
        public float $unitPurchasePrice,
        public float $margin,
        public ?string $batchNumber = null,
        public ?string $mfgDate = null,
        public ?string $expiryDate = null,
        public ?int $taxId = null,
        public string $status = 'pending',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            medicineId: (int) $data['medicine_id'],
            quantity: (int) ($data['quantity'] ?? 0),
            unitPurchasePrice: (float) ($data['unit_purchase_price'] ?? 0),
            margin: (float) ($data['margin'] ?? 0),
            batchNumber: filled($data['batch_number'] ?? null) ? $data['batch_number'] : null,
            mfgDate: $data['mfg_date'] ?? null,
            expiryDate: $data['expiry_date'] ?? null,
            taxId: filled($data['tax_id'] ?? null) ? (int) $data['tax_id'] : null,
            status: ($data['status'] ?? 'pending') === 'stocked' ? 'stocked' : 'pending',
        );
    }
}
