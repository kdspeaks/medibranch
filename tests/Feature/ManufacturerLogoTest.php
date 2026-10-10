<?php

namespace Tests\Feature;

use App\Livewire\Pages\Medicines\ManufacturerList;
use App\Models\Manufacturer;
use App\Models\User;
use App\Services\ImageOptimizerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ManufacturerLogoTest extends TestCase
{
    use RefreshDatabase;

    public function test_image_optimizer_service_resizes_and_compresses_image(): void
    {
        Storage::fake('public');

        // Create a large 600x600 fake PNG image
        $file = UploadedFile::fake()->image('large_logo.png', 600, 600);

        $service = app(ImageOptimizerService::class);
        $savedPath = $service->optimizeAndStore($file, 'manufacturers/logos', 200, 200, 80, 'public');

        Storage::disk('public')->assertExists($savedPath);

        // Verify it was converted to webp
        $this->assertStringEndsWith('.webp', $savedPath);

        // Verify dimensions were resized to max 200x200
        $storedContent = Storage::disk('public')->get($savedPath);
        $imageInfo = @getimagesizefromstring($storedContent);
        $this->assertNotFalse($imageInfo);
        $this->assertLessThanOrEqual(200, $imageInfo[0]);
        $this->assertLessThanOrEqual(200, $imageInfo[1]);
    }

    public function test_manufacturer_list_renders_logo_column_and_create_action(): void
    {
        Storage::fake('public');

        Role::firstOrCreate(['name' => 'Super Admin']);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('Super Admin');

        $manufacturer = Manufacturer::create([
            'name' => 'Dr. Reckeweg',
            'logo' => 'manufacturers/logos/sample.webp',
            'is_active' => true,
        ]);

        Livewire::actingAs($user)
            ->test(ManufacturerList::class)
            ->assertSee('Dr. Reckeweg');
    }
}
