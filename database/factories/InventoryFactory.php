<?php

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\Branch;
use App\Models\Medicine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inventory>
 */
class InventoryFactory extends Factory
{
    protected $model = Inventory::class;

    public function definition(): array
    {
        return [
            'branch_id'       => Branch::factory(),
            'medicine_id'     => Medicine::factory(),
            'stored_location' => $this->faker->optional()->word(),
        ];
    }
}
