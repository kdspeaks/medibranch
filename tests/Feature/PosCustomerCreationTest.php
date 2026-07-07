<?php

namespace Tests\Feature;

use App\Livewire\Pages\Sales\PosTerminal;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PosCustomerCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_customer_from_pos(): void
    {
        $branch = Branch::factory()->create();
        $admin = User::factory()->create();
        $admin->branches()->attach($branch);

        Livewire::actingAs($admin)
            ->test(PosTerminal::class)
            ->call('mountAction', 'createCustomer')
            ->set('mountedActions.0.data', [
                'name'  => 'New Walkin Customer',
                'phone' => '9876543210',
                'email' => 'walkin@example.com',
            ])
            ->call('callMountedAction')
            ->assertHasNoActionErrors()
            ->assertDispatched('customer-selected');

        $this->assertDatabaseHas('customers', [
            'name'  => 'New Walkin Customer',
            'phone' => '9876543210',
            'email' => 'walkin@example.com',
        ]);
    }
}
