<?php

namespace Tests\Feature;

use App\Livewire\Pages\Customers\CustomerView;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomerViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_view_displays_correct_stats(): void
    {
        $customer = Customer::factory()->create();
        $branch   = Branch::factory()->create();

        Role::firstOrCreate(['name' => 'Super Admin']);
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');
        $admin->branches()->attach($branch);

        Sale::factory()->create([
            'customer_id'    => $customer->id,
            'branch_id'      => $branch->id,
            'user_id'        => $admin->id,
            'total_amount'   => 100,
            'payment_status' => 'paid',
        ]);

        Sale::factory()->create([
            'customer_id'    => $customer->id,
            'branch_id'      => $branch->id,
            'user_id'        => $admin->id,
            'total_amount'   => 200,
            'payment_status' => 'paid',
        ]);

        Sale::factory()->create([
            'customer_id'    => $customer->id,
            'branch_id'      => $branch->id,
            'user_id'        => $admin->id,
            'total_amount'   => 300,
            'payment_status' => 'unpaid', // Should not be included in totalSpent
        ]);

        Livewire::actingAs($admin)
            ->test(CustomerView::class, ['customer' => $customer])
            ->assertSee('Customer: ' . $customer->name)
            ->assertSet('customer.id', $customer->id)
            ->assertViewHas('totalPurchases', 3)
            ->assertViewHas('totalSpent', 300); // 100 + 200
    }
}
