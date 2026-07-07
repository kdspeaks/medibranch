<?php

namespace App\Console\Commands;

use App\Models\Medicine;
use App\Models\InventoryBatch;
use App\Services\PricingService;
use Illuminate\Console\Command;

class SyncMedicinePrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:medicine-prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retroactively syncs medicine prices (sale and purchase) for medicines that have 0 or empty price based on their latest inventory batch.';

    /**
     * Execute the console command.
     */
    public function handle(PricingService $pricingService)
    {
        $this->info('Starting to sync medicine prices...');

        $medicines = Medicine::where('purchase_price', 0)
            ->orWhere('mrp', 0)
            ->orWhereNull('purchase_price')
            ->orWhereNull('mrp')
            ->get();

        if ($medicines->isEmpty()) {
            $this->info('No medicines found with missing prices.');
            return;
        }

        $this->info('Found ' . $medicines->count() . ' medicines with missing prices.');
        $updatedCount = 0;

        foreach ($medicines as $medicine) {
            // Get latest batch for this medicine across all branches
            $latestBatch = InventoryBatch::whereHas('inventory', function ($query) use ($medicine) {
                $query->where('medicine_id', $medicine->id);
            })->latest()->first();

            if ($latestBatch && $latestBatch->unit_purchase_price > 0) {
                $purchasePrice = (float) $latestBatch->unit_purchase_price;
                $mrp = (float) ($latestBatch->mrp ?? 0);
                $discountOnPurchase = (float) ($latestBatch->discount_on_purchase ?? 0);

                $medicine->update([
                    'purchase_price' => $purchasePrice,
                    'mrp' => $mrp,
                    'discount_on_purchase' => $discountOnPurchase,
                ]);

                $this->line("Updated Medicine ID {$medicine->id} ({$medicine->name}): Purchase Price -> {$purchasePrice}, MRP -> {$mrp}");
                $updatedCount++;
            } else {
                $this->warn("No inventory batch with a valid price found for Medicine ID {$medicine->id} ({$medicine->name}).");
            }
        }

        $this->info("Successfully updated prices for {$updatedCount} medicines.");
    }
}
