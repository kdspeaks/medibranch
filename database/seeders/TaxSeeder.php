<?php

namespace Database\Seeders;

use App\Models\Tax;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taxes = [
            ['name' => 'GST 0%', 'rate' => 0.00],
            ['name' => 'GST 5%', 'rate' => 5.00],
            ['name' => 'GST 12%', 'rate' => 12.00],
            ['name' => 'GST 18%', 'rate' => 18.00],
            ['name' => 'GST 28%', 'rate' => 28.00],
        ];

        foreach ($taxes as $data) {
            Tax::updateOrCreate(
                ['name' => $data['name']],
                ['rate' => $data['rate'], 'is_active' => true]
            );
        }
    }
}
