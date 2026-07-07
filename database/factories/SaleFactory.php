<?php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\Branch;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        return [
            'branch_id'      => Branch::factory(),
            'user_id'        => User::factory(),
            'customer_id'    => Customer::factory(),
            'invoice_number' => 'INV-TST-' . $this->faker->unique()->numberBetween(1000, 9999),
            'sale_date'      => now(),
            'sub_total'      => $this->faker->randomFloat(2, 50, 500),
            'discount'       => 0,
            'tax_amount'     => $this->faker->randomFloat(2, 5, 50),
            'round_off'      => 0,
            'total_amount'   => $this->faker->randomFloat(2, 55, 550),
            'payment_method' => $this->faker->randomElement(['cash', 'card', 'upi']),
            'payment_status' => 'paid',
        ];
    }
}
