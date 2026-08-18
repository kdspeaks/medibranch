<?php

namespace App\Console\Commands;

use App\Models\Medicine;
use App\Services\MedicineService;
use Illuminate\Console\Command;

class UpdateMedicineSkus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'medicines:update-skus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update SKUs for all existing medicines using the new intelligent brand logic';

    /**
     * Execute the console command.
     */
    public function handle(MedicineService $medicineService)
    {
        $medicines = Medicine::with(['manufacturer', 'medicineForm', 'medicineUnit'])->get();
        $count = 0;

        $this->info("Updating SKUs for {$medicines->count()} medicines...");

        $bar = $this->output->createProgressBar($medicines->count());

        foreach ($medicines as $medicine) {
            $newSku = $medicineService->generateSku(
                $medicine->name,
                $medicine->potency,
                $medicine->medicine_form_id,
                $medicine->packing_quantity,
                $medicine->medicine_unit_id,
                $medicine->manufacturer_id
            );

            if ($medicine->sku !== $newSku) {
                // Ensure SKU is unique if there are collisions
                $originalSku = $newSku;
                $counter = 1;
                while (Medicine::where('sku', $newSku)->where('id', '!=', $medicine->id)->exists()) {
                    $newSku = $originalSku . '-' . $counter;
                    $counter++;
                }

                $medicine->update(['sku' => $newSku]);
                $count++;
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Successfully updated {$count} SKUs.");
    }
}
