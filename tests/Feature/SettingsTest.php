<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake('public');

        $this->admin = User::factory()->create()->assignRole('Admin');
    }

    public function test_settings_page_renders(): void
    {
        $this->actingAs($this->admin)
            ->get('/settings')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Settings/Index')
                ->where('settings.app_name', 'Basic Admin'));
    }

    public function test_settings_can_be_updated_with_logo(): void
    {
        $this->actingAs($this->admin)
            ->put('/settings', [
                'app_name' => 'Acme Admin',
                'timezone' => 'Asia/Dhaka',
                'logo' => UploadedFile::fake()->image('logo.png', 64, 64),
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('Acme Admin', Setting::get('app_name'));
        $this->assertSame('Asia/Dhaka', Setting::get('timezone'));
        Storage::disk('public')->assertExists(Setting::get('logo_path'));

        $this->actingAs($this->admin)
            ->get('/dashboard')
            ->assertInertia(fn (Assert $page) => $page->where('app.name', 'Acme Admin'));
    }

    public function test_logo_can_be_removed(): void
    {
        $this->actingAs($this->admin)->put('/settings', [
            'app_name' => 'Acme',
            'timezone' => 'UTC',
            'logo' => UploadedFile::fake()->image('logo.png'),
        ]);
        $path = Setting::get('logo_path');

        $this->actingAs($this->admin)->put('/settings', ['app_name' => 'Acme', 'timezone' => 'UTC', 'remove_logo' => true]);

        $this->assertNull(Setting::get('logo_path'));
        Storage::disk('public')->assertMissing($path);
    }

    public function test_settings_are_validated(): void
    {
        $this->actingAs($this->admin)
            ->put('/settings', [
                'app_name' => '',
                'timezone' => 'Mars/Base',
                'logo' => UploadedFile::fake()->create('logo.svg', 10, 'image/svg+xml'),
            ])
            ->assertSessionHasErrors(['app_name', 'timezone', 'logo']);
    }

    public function test_users_without_permission_cannot_update_settings(): void
    {
        $user = User::factory()->create()->assignRole('User');

        $this->actingAs($user)->get('/settings')->assertForbidden();
        $this->actingAs($user)->put('/settings', ['app_name' => 'Hacked', 'timezone' => 'UTC'])->assertForbidden();
    }
}
