<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Medicine;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PurchaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MedicineViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_medicine_view_shows_stock_and_history_sections(): void
    {
        $branch = Branch::factory()->create();
        $supplier = Supplier::create(['name' => 'Alpha Supplier']);
        $medicine = Medicine::factory()->create([
            'name' => 'Arsenic Album',
            'barcode' => '1234567890123',
            'sku' => 'ARS-30CH',
        ]);

        app(PurchaseService::class)->save(\App\DTOs\PurchaseData::fromArray([
            'branch_id' => $branch->id,
            'supplier_id' => $supplier->id,
            'ref_code_prefix' => 'PO/',
            'ref_code_count' => '1',
            'purchase_date' => '2026-06-15',
            'status' => 'received',
            'items' => [[
                'medicine_id' => $medicine->id,
                'quantity' => 5,
                'unit_purchase_price' => 100,
                'mrp' => 100,
                'discountOnPurchase' => 20,
                'batch_number' => 'BATCH-1',
                'mfg_date' => '2026-01-01',
                'expiry_date' => '2026-12-31',
            ]],
        ]));

        $user = User::factory()->create(['email_verified_at' => now()]);
        $permission = Permission::firstOrCreate(['name' => 'manage-medicines']);
        $role = Role::firstOrCreate(['name' => 'Super Admin']);
        $role->givePermissionTo($permission);
        $user->assignRole($role);
        $user->branches()->attach($branch);

        $this->actingAs($user)
            ->get(route('medicines.view', ['medicine' => $medicine]))
            ->assertOk()
            ->assertSee('Arsenic Album')
            ->assertSee('Current Stock')
            ->assertSee('Stock Movements')
            ->assertSee('Purchase History');
    }
}
