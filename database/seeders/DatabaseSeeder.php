<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\Branch;
use App\Models\Medicine;
use App\Models\User;
use Database\Factories\MedicineFactory;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $userRole = Role::firstOrCreate(['name' => 'User']); // 🔁 prevent "RoleDoesNotExist" error

        // Create permissions
        $permissions = [
            'manage-users',
            'manage-roles-permission',
            'manage-settings',
            'manage-branches',
            'manage-medicines',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign all permissions to Super Admin
        $superAdminRole->givePermissionTo($permissions);

        // Create users
        User::factory(5)->create()->each(function ($user) use ($userRole) {
            $user->assignRole($userRole); // ✅ assign "User" role to factory-created users
        });

        // Create specific admin user
        $admin = User::factory()->create([
            'name' => 'Kunal Dutta',
            'email' => 'kdutta494@gmail.com',
        ]);
        $admin->assignRole($superAdminRole); // ✅ assign "Super Admin" role

        //Branch
        $branch = Branch::factory()->create();

        Setting::insert([
            [
                'key' => 'site_name',
                'value' => 'MediBranch',
                'type' => 'string',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'site_branch_id',
                'value' => $branch->id,
                'type' => 'integer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->call([
            ManufacturerSeeder::class,
            TaxSeeder::class,
        ]);
        
        $medicines = Medicine::factory(50)->create();
    }
}
