<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Medicine;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaleService
{
    public function __construct(
        protected PricingService $pricingService,
        protected InventoryService $inventoryService
    ) {
    }

    public function checkout(
        int $branchId,
        int $userId,
        ?int $customerId,
        array $cartItems,
        float $discount,
        string $paymentMethod,
        string $paymentStatus,
        ?string $paymentReference = null,
        bool $applyRoundOff = false,
        ?string $notes = null
    ): Sale {
        return DB::transaction(function () use (
            $branchId,
            $userId,
            $customerId,
            $cartItems,
            $discount,
            $paymentMethod,
            $paymentStatus,
            $paymentReference,
            $applyRoundOff,
            $notes
        ) {
            $branch = Branch::findOrFail($branchId);
            
            // Calculate totals
            $subTotal = 0;
            $taxAmount = 0;
            
            // Generate temporary invoice number to insert Sale first
            // Format: INV-{BRANCH_CODE}-{ID}
            $tempInvoiceNumber = 'INV-' . strtoupper(Str::slug($branch->code ?? $branch->name)) . '-' . time() . '-' . rand(100, 999);

            $sale = Sale::create([
                'branch_id' => $branchId,
                'user_id' => $userId,
                'customer_id' => $customerId,
                'invoice_number' => $tempInvoiceNumber,
                'sale_date' => now(),
                'sub_total' => 0, // Will update below
                'discount' => $discount,
                'tax_amount' => 0, // Will update below
                'round_off' => 0,
                'total_amount' => 0, // Will update below
                'payment_method' => $paymentMethod,
                'payment_reference' => $paymentReference,
                'payment_status' => $paymentStatus,
                'status' => 'completed',
                'notes' => $notes,
            ]);

            // Update with real ID
            $invoiceNumber = 'INV-' . strtoupper(Str::slug($branch->code ?? $branch->name)) . '-' . $sale->id;
            $sale->update(['invoice_number' => $invoiceNumber]);

            foreach ($cartItems as $item) {
                $medicine = Medicine::findOrFail($item['medicine_id']);
                
                // Ensure branch has enough stock (lockForUpdate is handled by inventoryService)
                // Deduct stock using FIFO or preferred batch
                $inventory = $this->inventoryService->stockOut(
                    branchId: $branchId,
                    medicineId: $medicine->id,
                    quantity: $item['quantity'],
                    reason: "Sale #{$invoiceNumber}",
                    preferredBatchId: $item['inventory_batch_id'] ?? null,
                    source: clone $sale // Will link item later
                );

                // Pricing calculation (Assuming cart already provided unit_price or we use medicine mrp)
                $unitPrice = $item['unit_price'] ?? $medicine->mrp;
                $pricing = $this->pricingService->lineWithTax(
                    $item['quantity'], 
                    $unitPrice, 
                    $branch->taxable ? $medicine->tax_id : null, 
                    $branch->taxable ? (bool) $medicine->is_tax_inclusive : false
                );

                $lineSubTotal = $pricing['line_sub_total'] ?? ($item['quantity'] * $unitPrice);
                $lineTaxAmount = $pricing['tax_amount'];
                $lineTotalAmount = $pricing['line_total_amount'];

                $saleItem = $sale->items()->create([
                    'medicine_id' => $medicine->id,
                    // If stockOut affected multiple batches, we ideally link the primary one, 
                    // but for simplicity in sale_items we can store the user's preferred batch 
                    // or the latest batch deducted. In InventoryService, the logs handle precise batches.
                    'inventory_batch_id' => $item['inventory_batch_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'margin' => 0, // Can be computed if needed
                    'tax_amount' => $lineTaxAmount,
                    'sub_total' => $lineSubTotal,
                    'total_amount' => $lineTotalAmount,
                ]);

                // Update logs source to be the specific sale item
                // The stockOut creates logs with $sale, we should update those specific logs to point to $saleItem
                // This ensures precise tracking.
                $medicineBatches = $inventory->batches()->get()->pluck('id');
                \App\Models\InventoryLog::where('source_type', Sale::class)
                    ->where('source_id', $sale->id)
                    ->where('reason', "Sale #{$invoiceNumber}")
                    ->whereIn('inventory_batch_id', $medicineBatches)
                    ->update([
                        'source_type' => SaleItem::class,
                        'source_id' => $saleItem->id,
                    ]);

                $subTotal += $lineSubTotal;
                $taxAmount += $lineTaxAmount;
            }

            $rawTotal = ($subTotal + $taxAmount) - $discount;
            $roundOff = 0;
            if ($applyRoundOff) {
                $roundedTotal = round($rawTotal);
                $roundOff = $roundedTotal - $rawTotal;
                $totalAmount = $roundedTotal;
            } else {
                $totalAmount = $rawTotal;
            }

            $sale->update([
                'sub_total' => $subTotal,
                'tax_amount' => $taxAmount,
                'round_off' => $roundOff,
                'total_amount' => $totalAmount,
            ]);

            return $sale->fresh(['items.medicine', 'customer']);
        });
    }
}
