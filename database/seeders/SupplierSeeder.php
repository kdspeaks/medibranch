<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'name'            => 'Herbal Remedies Ltd.',
                'contact_person'  => 'Rajesh Sharma',
                'email'           => 'rajesh@herbalremedies.com',
                'phone'           => '+91 9876543210',
                'address'         => '12 Green Park',
                'city'            => 'Kolkata',
                'state'           => 'West Bengal',
                'country'         => 'India',
                'postal_code'     => '700001',
            ],
            [
                'name'            => 'Nature’s Cure Supplies',
                'contact_person'  => 'Anita Verma',
                'email'           => 'anita@naturescure.com',
                'phone'           => '+91 9812345678',
                'address'         => '45 MG Road',
                'city'            => 'Mumbai',
                'state'           => 'Maharashtra',
                'country'         => 'India',
                'postal_code'     => '400001',
            ],
            [
                'name'            => 'Global Pharma Distributors',
                'contact_person'  => 'Sunil Kapoor',
                'email'           => 'sunil@globalpharma.com',
                'phone'           => '+91 9123456780',
                'address'         => '88 Nehru Place',
                'city'            => 'Delhi',
                'state'           => 'Delhi',
                'country'         => 'India',
                'postal_code'     => '110019',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}

