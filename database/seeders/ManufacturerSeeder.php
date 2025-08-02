<?php
namespace Database\Seeders;

use App\Models\Manufacturer;
use Illuminate\Database\Seeder;

class ManufacturerSeeder extends Seeder
{
    public function run(): void
    {
        $manufacturers = [
            [
                'name' => 'SBL Pvt. Ltd.',
                'contact_name' => 'Mr. Rajiv Kapoor',
                'phone' => '+91-11-43270100',
                'email' => 'info@sblglobal.in',
                'address' => 'D-157, Okhla Industrial Area Phase-I, New Delhi, India',
                'website' => 'https://www.sblglobal.in',
                'country' => 'India',
                'is_active' => true,
            ],
            [
                'name' => 'Dr. Reckeweg & Co. GmbH',
                'contact_name' => 'Dr. Jürgen Reckeweg',
                'phone' => '+49-6251-10930',
                'email' => 'info@reckeweg.de',
                'address' => 'Berliner Ring 32, 64625 Bensheim, Germany',
                'website' => 'https://www.reckeweg.de',
                'country' => 'Germany',
                'is_active' => true,
            ],
            [
                'name' => 'Boiron Laboratories',
                'contact_name' => 'Ms. Véronique De la Butte',
                'phone' => '+33-4-7270-6060',
                'email' => 'contact@boiron.fr',
                'address' => '2 Avenue de l\'Ouest Lyonnais, 69510 Messimy, France',
                'website' => 'https://www.boiron.com',
                'country' => 'France',
                'is_active' => true,
            ],
            [
                'name' => 'Schwabe India',
                'contact_name' => 'Dr. Anil Khurana',
                'phone' => '+91-120-4016500',
                'email' => 'info@schwabeindia.com',
                'address' => 'A-36, Sector-60, Noida, Uttar Pradesh, India',
                'website' => 'https://www.schwabeindia.com',
                'country' => 'India',
                'is_active' => true,
            ],
            [
                'name' => 'Allen Homoeo & Herbal Products Ltd.',
                'contact_name' => 'Mr. Pankaj Patel',
                'phone' => '+91-731-4024885',
                'email' => 'allenhomoeo@gmail.com',
                'address' => '60 Feet Road, Rajendra Nagar, Indore, MP, India',
                'website' => 'https://www.allenhmp.com',
                'country' => 'India',
                'is_active' => true,
            ],
        ];

        foreach ($manufacturers as $data) {
            Manufacturer::create($data);
        }
    }
}
