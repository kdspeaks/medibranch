<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Branch>
 */
class BranchFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Sanjibani Branch',
            'code' => strtoupper($this->faker->unique()->lexify('???-??')),
            'address' => $this->faker->address,
            'phone' => $this->faker->optional()->phoneNumber,
            'email' => $this->faker->optional()->safeEmail(),
            'is_active' => true, // 90% active
        ];
    }
}
