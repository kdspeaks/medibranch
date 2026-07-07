<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'name'           => $this->faker->company(),
            'contact_person' => $this->faker->name(),
            'email'          => $this->faker->unique()->safeEmail(),
            'phone'          => $this->faker->phoneNumber(),
            'address'        => $this->faker->address(),
            'city'           => $this->faker->city(),
            'state'          => $this->faker->state(),
            'postal_code'    => $this->faker->postcode(),
            'country'        => $this->faker->country(),
            'website'        => $this->faker->optional()->url(),
            'is_active'      => true,
        ];
    }
}
