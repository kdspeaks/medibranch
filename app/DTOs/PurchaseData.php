<?php

namespace App\DTOs;

readonly class PurchaseData
{
    /**
     * @param PurchaseItemData[] $items
     */
    public function __construct(
        public int $branchId,
        public ?int $supplierId,
        public ?string $invoiceNumber,
        public string $purchaseDate,
        public string $status,
        public ?string $notes,
        public ?string $refCodePrefix,
        public ?int $refCodeCount,
        public array $items = [],
    ) {}

    public static function fromArray(array $data): self
    {
        $items = array_map(
            fn(array $item) => PurchaseItemData::fromArray($item),
            array_filter($data['items'] ?? [], fn($item) => !empty($item['medicine_id']) && (int)($item['quantity'] ?? 0) > 0)
        );

        return new self(
            branchId: (int) $data['branch_id'],
            supplierId: filled($data['supplier_id'] ?? null) ? (int) $data['supplier_id'] : null,
            invoiceNumber: $data['invoice_number'] ?? null,
            purchaseDate: $data['purchase_date'] ?? now()->toDateString(),
            status: match ($data['status'] ?? 'draft') {
                'pending' => 'draft',
                'completed' => 'received',
                default => $data['status'] ?? 'draft',
            },
            notes: $data['notes'] ?? null,
            refCodePrefix: $data['ref_code_prefix'] ?? null,
            refCodeCount: filled($data['ref_code_count'] ?? null) ? (int) $data['ref_code_count'] : null,
            items: array_values($items),
        );
    }
}
