<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $userRole = Role::firstOrCreate(['name' => 'User']);

        $permissions = [
            'manage-users',
            'manage-suppliers',
            'manage-medicines',
            'manage-manufacturers',
            'manage-purchases',
            'manage-roles-permission',
            'manage-settings',
            'manage-branches',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $superAdminRole->syncPermissions($permissions);

        $this->call([
            BranchSeeder::class,
            SettingSeeder::class,
            ManufacturerSeeder::class,
            SupplierSeeder::class,
            TaxSeeder::class,
            MedicineSeeder::class,
        ]);

        $branches = Branch::query()->where('is_active', true)->get();

        $admin = User::query()->firstOrCreate(
            ['email' => 'kdutta494@gmail.com'],
            [
                'name' => 'Kunal Dutta',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ],
        );
        $admin->syncRoles([$superAdminRole]);
        $admin->branches()->syncWithoutDetaching($branches->pluck('id')->all());

        $staffUsers = User::factory(3)->create();

        foreach ($staffUsers as $index => $user) {
            $user->syncRoles([$userRole]);

            if ($branch = $branches->get($index % max($branches->count(), 1))) {
                $user->branches()->syncWithoutDetaching([$branch->id]);
            }
        }

        $this->call([
            PurchaseSeeder::class,
        ]);
    }
}
