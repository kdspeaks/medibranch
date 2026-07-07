<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Medicine;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function __construct(
        private readonly InventoryService $inventoryService,
    ) {}

    public function run(): void
    {
        $branches = Branch::where('is_active', true)->get();
        $medicines = Medicine::where('is_active', true)->get();

        foreach ($branches as $branch) {
            foreach ($medicines as $medicine) {
                // Stock in some medicine directly, outside of a purchase
                $this->inventoryService->stockIn(
                    branchId: $branch->id,
                    medicineId: $medicine->id,
                    quantity: rand(10, 50),
                    purchasePrice: (float) $medicine->purchase_price,
                    mrp: (float) $medicine->mrp,
                    discountOnPurchase: (float) $medicine->discount_on_purchase,
                    reason: 'Initial Stock',
                    batchNumber: 'INIT-'.strtoupper(uniqid()),
                    mfgDate: now()->subMonths(rand(1, 12))->toDateString(),
                    expiryDate: now()->addMonths(rand(12, 36))->toDateString(),
                );
            }
        }
    }
}
