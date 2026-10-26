<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_users_without_permission_are_forbidden(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/dashboard')
            ->assertForbidden()
            ->assertInertia(fn (Assert $page) => $page->component('Error')->where('status', 403));
    }

    public function test_unknown_pages_render_the_error_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/does-not-exist')
            ->assertNotFound()
            ->assertInertia(fn (Assert $page) => $page->component('Error')->where('status', 404));
    }

    public function test_users_with_permission_see_dashboard(): void
    {
        $user = User::factory()->create()->assignRole('User');

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->has('stats', fn (Assert $stats) => $stats->where('users', 1)->etc())
                ->has('recentUsers', 1)
                ->has('roleBreakdown', 3)
                ->where('auth.user.permissions', ['dashboard.view'])
            );
    }

    public function test_super_admin_passes_every_permission_check(): void
    {
        $admin = User::factory()->create()->assignRole(User::SUPER_ADMIN_ROLE);

        $this->actingAs($admin)->get('/dashboard')->assertOk();
        $this->assertTrue($admin->can('settings.update'));
    }
}
