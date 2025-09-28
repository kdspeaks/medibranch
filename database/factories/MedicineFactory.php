<?php

namespace Database\Factories;

use App\Models\Tax;
use App\Models\Medicine;
use Illuminate\Support\Str;
use App\Models\Manufacturer;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicineFactory extends Factory
{
    protected $model = Medicine::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->word() . ' ' . $this->faker->randomElement(['Album', 'Tincture', 'Plus', 'Mix']);

        return [
            'name' => $name,
            'barcode' => Str::upper(Str::random(12)), // or use a fixed pattern if needed
            'sku' => strtoupper($this->faker->unique()->lexify('SKU-??????')),

            'manufacturer_id' => Manufacturer::inRandomOrder()->value('id') ?? null,
            'tax_id' => Tax::inRandomOrder()->value('id') ?? null,

            'potency' => $this->faker->randomElement(['30CH', '200CH', '1M', '10M']),
            'form' => $this->faker->randomElement(['Dilution', 'Tablet', 'Syrup', 'Ointment']),

            'packing_quantity' => $this->faker->numberBetween(10, 100),
            'packing_unit' => $this->faker->randomElement(['ml', 'g', 'pcs', 'bottle']),

            'purchase_price' => $this->faker->randomFloat(2, 5, 500),
            'margin' => $this->faker->randomFloat(2, 2, 20),

            'description' => $this->faker->optional()->sentence(),
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
        ];
    }
}
