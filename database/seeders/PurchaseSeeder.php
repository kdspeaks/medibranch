<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Illuminate\Database\Seeder;

class PurchaseSeeder extends Seeder
{
    public function __construct(
        private readonly PurchaseService $purchaseService,
    ) {
    }

    public function run(): void
    {
        if (Purchase::query()->exists()) {
            return;
        }

        $branches = Branch::query()->where('is_active', true)->get();
        $suppliers = Supplier::query()->get();
        $medicines = Medicine::query()->take(9)->get()->chunk(3);

        foreach ($branches as $branchIndex => $branch) {
            $items = [];
            $medicineGroup = $medicines->get($branchIndex % max($medicines->count(), 1), collect());

            foreach ($medicineGroup as $medicineIndex => $medicine) {
                $items[] = [
                    'medicine_id' => $medicine->id,
                    'quantity' => 10 + ($medicineIndex * 5),
                    'unit_purchase_price' => (float) $medicine->purchase_price,
                    'margin' => (float) $medicine->margin,
                    'batch_number' => sprintf('B%s%02d', $branch->id, $medicineIndex + 1),
                    'mfg_date' => now()->subMonths(3 + $medicineIndex)->toDateString(),
                    'expiry_date' => now()->addMonths(9 + $medicineIndex)->toDateString(),
                    'tax_id' => $medicine->tax_id,
                ];
            }

            $this->purchaseService->save([
                'branch_id' => $branch->id,
                'supplier_id' => $suppliers->get($branchIndex % max($suppliers->count(), 1))?->id,
                'ref_code_prefix' => 'PO/',
                'ref_code_count' => (string) ($branchIndex + 1),
                'invoice_number' => 'INV-' . str_pad((string) ($branchIndex + 1), 4, '0', STR_PAD_LEFT),
                'purchase_date' => now()->subDays($branchIndex + 1)->toDateString(),
                'status' => 'received',
                'notes' => 'Seeder generated purchase.',
                'items' => $items,
            ]);
        }
    }
}
