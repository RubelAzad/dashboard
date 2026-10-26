<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_guests_cannot_view_profile(): void
    {
        $this->get('/profile')->assertRedirect('/login');
    }

    public function test_users_without_any_permission_can_view_their_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Profile/Edit')
                ->where('profile.email', $user->email)
            );
    }

    public function test_users_can_update_their_details(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/profile', ['name' => 'New Name', 'email' => 'new@example.com'])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame('New Name', $user->fresh()->name);
        $this->assertSame('new@example.com', $user->fresh()->email);
    }

    public function test_email_must_be_unique(): void
    {
        $other = User::factory()->create();

        $this->actingAs(User::factory()->create())
            ->patch('/profile', ['name' => 'Name', 'email' => $other->email])
            ->assertSessionHasErrors('email');
    }

    public function test_users_can_change_their_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => 'password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => 'wrong-password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertSessionHasErrorsIn('password', 'current_password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }
}
