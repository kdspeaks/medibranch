<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\Tax;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\PurchaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FoundationCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_receive_creates_batch_log_and_links_purchase_item(): void
    {
        $branch = Branch::factory()->create();
        $tax = Tax::create(['name' => 'GST 10', 'rate' => 10, 'is_active' => true]);
        $medicine = Medicine::factory()->create([
            'tax_id' => $tax->id,
            'purchase_price' => 100,
            'mrp' => 120,
            'discount_on_purchase' => 10,
        ]);

        $purchase = app(PurchaseService::class)->save(\App\DTOs\PurchaseData::fromArray([
            'branch_id' => $branch->id,
            'ref_code_prefix' => 'PO/',
            'ref_code_count' => '1',
            'purchase_date' => '2026-06-13',
            'status' => 'received',
            'items' => [[
                'medicine_id' => $medicine->id,
                'quantity' => 2,
                'unit_purchase_price' => 100,
                'mrp' => 120,
                'discountOnPurchase' => 10,
                'batch_number' => 'B1',
                'mfg_date' => '2026-01-01',
                'expiry_date' => '2027-01-01',
                'tax_id' => $tax->id,
            ]],
        ]));

        $item = $purchase->items()->first();
        $batch = $item->inventoryBatch;

        $this->assertSame('received', $purchase->status);
        $this->assertSame('stocked', $item->status);
        $this->assertNotNull($batch);
        $this->assertSame('2026-01-01', $batch->mfg_date->toDateString());
        $this->assertSame('2027-01-01', $batch->expiry_date->toDateString());
        $this->assertDatabaseHas('inventory_logs', [
            'inventory_batch_id' => $batch->id,
            'type' => 'in',
            'quantity' => 2,
            'source_type' => $item->getMorphClass(),
            'source_id' => $item->id,
        ]);
    }

    public function test_stock_out_uses_fifo_by_expiry_date(): void
    {
        $branch = Branch::factory()->create();
        $medicine = Medicine::factory()->create(['mrp' => 50, 'discount_on_purchase' => 10]);
        $service = app(InventoryService::class);

        $later = $service->stockIn($branch->id, $medicine->id, 5, 20, 50, 10, 'test', 'LATER', null, '2028-01-01');
        $earlier = $service->stockIn($branch->id, $medicine->id, 3, 20, 50, 10, 'test', 'EARLIER', null, '2027-01-01');

        $service->stockOut($branch->id, $medicine->id, 4, 'test_fifo');

        $this->assertSame(0, $earlier->fresh()->available_quantity);
        $this->assertSame(4, $later->fresh()->available_quantity);
    }

    public function test_branch_restricted_user_cannot_access_other_branch_purchase(): void
    {
        Role::firstOrCreate(['name' => 'User']);

        $allowedBranch = Branch::factory()->create();
        $otherBranch = Branch::factory()->create();
        $user = User::create([
            'name' => 'Branch User',
            'email' => 'branch@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $user->assignRole('User');
        $user->branches()->attach($allowedBranch);

        $purchase = Purchase::create([
            'branch_id' => $otherBranch->id,
            'ref_code_count' => '1',
            'purchase_date' => '2026-06-13',
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->get(route('medicines.purchases.view', ['purchase' => $purchase]))
            ->assertForbidden();
    }

    public function test_medicine_uses_mrp_column(): void
    {
        $medicine = Medicine::factory()->create(['mrp' => 123.45]);

        $this->assertSame('123.45', $medicine->fresh()->mrp);
    }
}
