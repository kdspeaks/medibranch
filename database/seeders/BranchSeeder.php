<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            [
                'name' => 'MediBranch Central',
                'code' => 'MEDB-CEN',
                'address' => '12 College Street, Kolkata, West Bengal',
                'phone' => '+91-33-4000-1001',
                'email' => 'central@medibranch.test',
                'is_active' => true,
            ],
            [
                'name' => 'MediBranch North',
                'code' => 'MEDB-NTH',
                'address' => '44 BT Road, Kolkata, West Bengal',
                'phone' => '+91-33-4000-1002',
                'email' => 'north@medibranch.test',
                'is_active' => true,
            ],
            [
                'name' => 'MediBranch South',
                'code' => 'MEDB-STH',
                'address' => '88 Gariahat Road, Kolkata, West Bengal',
                'phone' => '+91-33-4000-1003',
                'email' => 'south@medibranch.test',
                'is_active' => true,
            ],
        ];

        foreach ($branches as $branch) {
            Branch::updateOrCreate(
                ['code' => $branch['code']],
                $branch,
            );
        }
    }
}
