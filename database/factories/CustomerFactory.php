<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name'    => $this->faker->name(),
            'phone'   => $this->faker->unique()->numerify('9#########'),
            'email'   => $this->faker->optional()->safeEmail(),
            'address' => $this->faker->optional()->address(),
        ];
    }
}
