<?php

namespace Database\Factories;

use App\Models\Tax;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tax>
 */
class TaxFactory extends Factory
{
    protected $model = Tax::class;

    public function definition(): array
    {
        return [
            'name'      => $this->faker->unique()->word() . ' Tax',
            'rate'      => $this->faker->randomFloat(2, 5, 20),
            'is_active' => true,
        ];
    }
}
