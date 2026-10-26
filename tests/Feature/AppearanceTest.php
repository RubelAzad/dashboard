<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Appearance;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AppearanceTest extends TestCase
{
    use RefreshDatabase;

    private const THEME = [
        'design' => 'floating',
        'accent' => 'rose',
        'neutral' => 'stone',
        'font' => 'manrope',
        'icons' => 'bold',
        'radius' => 'round',
        'density' => 'compact',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_defaults_are_shared_and_rendered_on_the_html_element(): void
    {
        $this->get('/login')
            ->assertSee('data-design="midnight"', false)
            ->assertSee('data-accent="indigo"', false)
            ->assertInertia(fn (Assert $page) => $page
                ->where('theme.defaults', Appearance::DEFAULTS)
                ->where('theme.mine', null)
                ->where('theme.allowCustom', true)
            );
    }

    public function test_any_user_can_save_and_reset_a_personal_theme(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/appearance', self::THEME)->assertSessionHasNoErrors();
        $this->assertSame(self::THEME, $user->fresh()->preferences['appearance']);

        $this->actingAs($user->fresh())->get('/profile')
            ->assertSee('data-design="floating"', false)
            ->assertInertia(fn (Assert $page) => $page->where('theme.mine', self::THEME));

        $this->actingAs($user->fresh())->delete('/appearance');
        $this->assertNull($user->fresh()->preferences);
    }

    public function test_invalid_values_are_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->patch('/appearance', [...self::THEME, 'accent' => 'neon', 'font' => 'comic-sans'])
            ->assertSessionHasErrors(['accent', 'font']);
    }

    public function test_only_users_with_appearance_permission_can_change_the_default(): void
    {
        $payload = ['appearance' => self::THEME, 'allow_user_themes' => true];

        $admin = User::factory()->create()->assignRole('Admin');
        $this->actingAs($admin)->put('/settings/appearance', $payload)->assertForbidden();

        $superAdmin = User::factory()->create()->assignRole(User::SUPER_ADMIN_ROLE);
        $this->actingAs($superAdmin)->put('/settings/appearance', $payload)->assertSessionHasNoErrors();

        $this->assertSame(self::THEME, Appearance::defaults());
    }

    public function test_turning_off_personal_themes_forces_the_default(): void
    {
        $user = User::factory()->create(['preferences' => ['appearance' => self::THEME]]);
        $superAdmin = User::factory()->create()->assignRole(User::SUPER_ADMIN_ROLE);

        $this->actingAs($superAdmin)->put('/settings/appearance', [
            'appearance' => Appearance::DEFAULTS,
            'allow_user_themes' => false,
        ]);

        $this->assertSame(Appearance::DEFAULTS, Appearance::effective($user));
        $this->actingAs($user)->patch('/appearance', self::THEME)->assertForbidden();
    }
}
