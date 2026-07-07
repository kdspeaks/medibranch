<?php

namespace Database\Factories;

use App\Models\Purchase;
use App\Models\Branch;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Purchase>
 */
class PurchaseFactory extends Factory
{
    protected $model = Purchase::class;

    public function definition(): array
    {
        return [
            'branch_id'       => Branch::factory(),
            'supplier_id'     => Supplier::factory(),
            'invoice_number'  => 'PO-' . $this->faker->unique()->numberBetween(1000, 9999),
            'purchase_date'   => now(),
            'total_amount'    => $this->faker->randomFloat(2, 100, 1000),
            'status'          => 'received',
            'notes'           => $this->faker->optional()->sentence(),
            'ref_code_prefix' => 'PO/',
            'ref_code_count'  => $this->faker->numberBetween(1, 100),
        ];
    }
}
