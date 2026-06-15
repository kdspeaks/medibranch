<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::query()->where('is_active', true)->orderBy('id')->first();

        Setting::updateOrCreate(
            ['key' => 'site_name'],
            ['value' => 'MediBranch', 'type' => 'string'],
        );

        if ($branch) {
            Setting::updateOrCreate(
                ['key' => 'site_branch_id'],
                ['value' => (string) $branch->id, 'type' => 'integer'],
            );
        }
    }
}
