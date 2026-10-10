<?php

namespace Tests\Feature;

use App\Livewire\Pages\Medicines\MedicineList;
use App\Models\Branch;
use App\Models\Medicine;
use App\Models\MedicineUnit;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MedicineListTest extends TestCase
{
    use RefreshDatabase;

    public function test_medicine_list_differentiates_medicines_of_same_name_by_quantity_and_potency(): void
    {
        Role::firstOrCreate(['name' => 'Super Admin']);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('Super Admin');

        $unit = MedicineUnit::create(['name' => 'ml']);

        $medSmall = Medicine::factory()->create([
            'name' => 'Arnica Montana',
            'potency' => '30 CH',
            'packing_quantity' => 30,
            'medicine_unit_id' => $unit->id,
            'sku' => 'ARN-30-30ML',
            'barcode' => 'BAR-001',
        ]);

        $medLarge = Medicine::factory()->create([
            'name' => 'Arnica Montana',
            'potency' => '200 CH',
            'packing_quantity' => 100,
            'medicine_unit_id' => $unit->id,
            'sku' => 'ARN-200-100ML',
            'barcode' => 'BAR-002',
        ]);

        Livewire::actingAs($user)
            ->test(MedicineList::class)
            ->assertSee('Arnica Montana')
            ->assertSee('30 ml')
            ->assertSee('100 ml')
            ->assertSee('30 CH')
            ->assertSee('200 CH')
            ->assertSee('ARN-30-30ML')
            ->assertSee('ARN-200-100ML');
    }

    public function test_medicine_list_shows_stock_breakdown_across_branches_when_all_branches_selected(): void
    {
        Role::firstOrCreate(['name' => 'Super Admin']);

        $branchA = Branch::factory()->create(['name' => 'Central Pharmacy', 'is_active' => true]);
        $branchB = Branch::factory()->create(['name' => 'North Branch', 'is_active' => true]);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('Super Admin');
        $user->branches()->attach([$branchA->id, $branchB->id]);

        $medicine = Medicine::factory()->create([
            'name' => 'Belladonna',
            'potency' => '30 CH',
            'packing_quantity' => 30,
            'barcode' => 'BAR-003',
            'sku' => 'BEL-30-30ML',
        ]);

        $inventoryService = app(InventoryService::class);
        $inventoryService->stockIn($branchA->id, $medicine->id, 10, 50, 80, 0);
        $inventoryService->stockIn($branchB->id, $medicine->id, 5, 50, 80, 0);

        // All branches view (filter cleared / null)
        Livewire::actingAs($user)
            ->test(MedicineList::class)
            ->set('tableFilters.branch_id.value', null)
            ->assertSee('Total: 15')
            ->assertSee('Central Pharmacy:')
            ->assertSee('North Branch:');

        // Scoped to single branch
        Livewire::actingAs($user)
            ->test(MedicineList::class)
            ->set('tableFilters.branch_id.value', $branchA->id)
            ->assertSee('10')
            ->assertDontSee('Total: 15');
    }

    public function test_medicine_list_sorts_by_last_sale_descending_by_default(): void
    {
        Role::firstOrCreate(['name' => 'Super Admin']);

        $branch = Branch::factory()->create(['is_active' => true]);
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('Super Admin');
        $user->branches()->attach($branch);

        $medOlderSale = Medicine::factory()->create([
            'name' => 'Aconite Napellus',
            'barcode' => 'BAR-OLDER',
            'sku' => 'ACO-001',
        ]);

        $medRecentSale = Medicine::factory()->create([
            'name' => 'Bryonia Alba',
            'barcode' => 'BAR-RECENT',
            'sku' => 'BRY-002',
        ]);

        // Sale 2 days ago for Aconite
        $saleOlder = \App\Models\Sale::create([
            'branch_id' => $branch->id,
            'user_id' => $user->id,
            'invoice_number' => 'INV-TEST-001',
            'sub_total' => 100,
            'total_amount' => 100,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'sale_date' => now()->subDays(2),
        ]);
        \App\Models\SaleItem::create([
            'sale_id' => $saleOlder->id,
            'medicine_id' => $medOlderSale->id,
            'quantity' => 1,
            'unit_price' => 100,
            'sub_total' => 100,
            'total_amount' => 100,
        ]);

        // Sale 1 hour ago for Bryonia
        $saleRecent = \App\Models\Sale::create([
            'branch_id' => $branch->id,
            'user_id' => $user->id,
            'invoice_number' => 'INV-TEST-002',
            'sub_total' => 100,
            'total_amount' => 100,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'sale_date' => now()->subHour(),
        ]);
        \App\Models\SaleItem::create([
            'sale_id' => $saleRecent->id,
            'medicine_id' => $medRecentSale->id,
            'quantity' => 1,
            'unit_price' => 100,
            'sub_total' => 100,
            'total_amount' => 100,
        ]);

        Livewire::actingAs($user)
            ->test(MedicineList::class)
            ->assertSeeInOrder(['Bryonia Alba', 'Aconite Napellus']);
    }
}
