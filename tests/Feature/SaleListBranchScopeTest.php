<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Medicine;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SaleListBranchScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_user_only_sees_their_branch_sales(): void
    {
        Role::firstOrCreate(['name' => 'User']);

        $ownBranch = Branch::factory()->create();
        $otherBranch = Branch::factory()->create();

        $user = User::create([
            'name' => 'Branch User',
            'email' => 'branch@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $user->assignRole('User');
        $user->branches()->attach($ownBranch);

        $medicine = Medicine::factory()->create(['mrp' => 50, 'discount_on_purchase' => 10]);

        $inventoryService = app(InventoryService::class);
        $inventoryService->stockIn($ownBranch->id, $medicine->id, 20, 30, 50, 10);
        $inventoryService->stockIn($otherBranch->id, $medicine->id, 20, 30, 50, 10);

        $saleService = app(SaleService::class);

        $ownSale = $saleService->checkout(
            branchId: $ownBranch->id,
            userId: $user->id,
            customerId: null,
            cartItems: [['medicine_id' => $medicine->id, 'quantity' => 1, 'unit_price' => 50]],
            discount: 0,
            paymentMethod: 'cash',
            paymentStatus: 'paid',
        );

        $anotherUser = User::factory()->create();
        $otherSale = $saleService->checkout(
            branchId: $otherBranch->id,
            userId: $anotherUser->id,
            customerId: null,
            cartItems: [['medicine_id' => $medicine->id, 'quantity' => 1, 'unit_price' => 50]],
            discount: 0,
            paymentMethod: 'cash',
            paymentStatus: 'paid',
        );

        Livewire::actingAs($user)
            ->test(\App\Livewire\Pages\Sales\SaleList::class)
            ->assertSee($ownSale->invoice_number)
            ->assertDontSee($otherSale->invoice_number);
    }

    public function test_super_admin_sees_all_branch_sales(): void
    {
        Role::firstOrCreate(['name' => 'Super Admin']);

        $branchA = Branch::factory()->create();
        $branchB = Branch::factory()->create();

        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole('Super Admin');
        $superAdmin->branches()->attach($branchA);

        $medicine = Medicine::factory()->create(['mrp' => 50, 'discount_on_purchase' => 10]);

        $inventoryService = app(InventoryService::class);
        $inventoryService->stockIn($branchA->id, $medicine->id, 20, 30, 50, 10);
        $inventoryService->stockIn($branchB->id, $medicine->id, 20, 30, 50, 10);

        $saleService = app(SaleService::class);
        $regularUser = User::factory()->create();

        $saleA = $saleService->checkout(
            branchId: $branchA->id,
            userId: $superAdmin->id,
            customerId: null,
            cartItems: [['medicine_id' => $medicine->id, 'quantity' => 1, 'unit_price' => 50]],
            discount: 0,
            paymentMethod: 'cash',
            paymentStatus: 'paid',
        );

        $saleB = $saleService->checkout(
            branchId: $branchB->id,
            userId: $regularUser->id,
            customerId: null,
            cartItems: [['medicine_id' => $medicine->id, 'quantity' => 1, 'unit_price' => 50]],
            discount: 0,
            paymentMethod: 'cash',
            paymentStatus: 'paid',
        );

        Livewire::actingAs($superAdmin)
            ->test(\App\Livewire\Pages\Sales\SaleList::class)
            ->assertSee($saleA->invoice_number)
            ->assertSee($saleB->invoice_number);
    }
}
