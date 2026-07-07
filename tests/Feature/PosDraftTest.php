<?php

namespace Tests\Feature;

use App\Livewire\Pages\Sales\PosTerminal;
use App\Models\Branch;
use App\Models\Medicine;
use App\Models\PosDraft;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PosDraftTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_hold_load_and_delete_pos_draft(): void
    {
        $branch = Branch::factory()->create();
        $admin = User::factory()->create();
        $admin->branches()->attach($branch);

        $medicine = Medicine::factory()->create(['mrp' => 100, 'discount_on_purchase' => 10]);

        $checkoutData = [
            'cart' => [
                [
                    'medicine_id' => $medicine->id,
                    'name'        => $medicine->name,
                    'unit_price'  => 100,
                    'quantity'    => 2,
                    'tax_rate'    => 0,
                ],
            ],
            'discount'      => 0,
            'applyRoundOff' => true,
        ];

        // 1. Test Holding Draft
        Livewire::actingAs($admin)
            ->test(PosTerminal::class)
            ->call('holdInvoice', $checkoutData)
            ->assertDispatched('draft-saved');

        $this->assertDatabaseCount('pos_drafts', 1);
        $draft = PosDraft::first();
        $this->assertEquals(200, $draft->total_amount);

        // 2. Test Loading Draft
        Livewire::actingAs($admin)
            ->test(PosTerminal::class)
            ->call('loadDraft', $draft->id)
            ->assertDispatched('draft-loaded');

        // Draft should be auto-deleted after loading
        $this->assertDatabaseCount('pos_drafts', 0);

        // 3. Test Deleting Draft explicitly
        $draft2 = PosDraft::create([
            'branch_id'      => $branch->id,
            'user_id'        => $admin->id,
            'cart_data'      => $checkoutData,
            'total_amount'   => 200,
            'reference_name' => 'Test Draft',
        ]);

        $this->assertDatabaseCount('pos_drafts', 1);

        Livewire::actingAs($admin)
            ->test(PosTerminal::class)
            ->call('deleteDraft', $draft2->id);

        $this->assertDatabaseCount('pos_drafts', 0);
    }
}
