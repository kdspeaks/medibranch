<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Medicine;
use App\Models\User;
use App\Models\Customer;
use App\Models\Inventory;
use App\Services\SaleService;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_creates_sale_and_deducts_inventory()
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create();
        $customer = Customer::create(['name' => 'John Doe', 'phone' => '123456']);
        
        $medicine = Medicine::factory()->create(['sale_price' => 100]);
        
        // Stock in 50 units
        $inventoryService = app(InventoryService::class);
        $inventoryService->stockIn(
            branchId: $branch->id,
            medicineId: $medicine->id,
            quantity: 50,
            purchasePrice: 80,
            margin: 25
        );
        
        $saleService = app(SaleService::class);
        
        $cartItems = [
            [
                'medicine_id' => $medicine->id,
                'quantity' => 5,
                'unit_price' => 100,
            ]
        ];
        
        $sale = $saleService->checkout(
            branchId: $branch->id,
            userId: $user->id,
            customerId: $customer->id,
            cartItems: $cartItems,
            discount: 50,
            paymentMethod: 'cash',
            paymentStatus: 'paid'
        );
        
        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'branch_id' => $branch->id,
            'total_amount' => 450, // 5 * 100 = 500 - 50 discount
        ]);
        
        $this->assertDatabaseHas('sale_items', [
            'sale_id' => $sale->id,
            'medicine_id' => $medicine->id,
            'quantity' => 5,
        ]);
        
        // Check inventory deduction
        $inventory = Inventory::where('branch_id', $branch->id)->where('medicine_id', $medicine->id)->first();
        $this->assertEquals(45, $inventory->quantity);
        $this->assertEquals(45, $inventory->batches()->first()->available_quantity);
    }
}
