<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->superAdmin = User::factory()->create(['name' => 'Root'])->assignRole(User::SUPER_ADMIN_ROLE);
        $this->admin = User::factory()->create(['name' => 'Manager'])->assignRole('Admin');
    }

    public function test_index_lists_and_searches_users(): void
    {
        User::factory()->create(['name' => 'Findable Person']);

        $this->actingAs($this->admin)
            ->get('/users?search=Findable')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Users/Index')
                ->has('users.data', 1)
                ->where('users.data.0.name', 'Findable Person')
                ->where('filters.search', 'Findable'));
    }

    public function test_index_is_paginated(): void
    {
        User::factory()->count(15)->create();

        $this->actingAs($this->admin)
            ->get('/users')
            ->assertInertia(fn (Assert $page) => $page->has('users.data', 10)->where('users.total', 17));
    }

    public function test_users_without_permission_cannot_manage_users(): void
    {
        $user = User::factory()->create()->assignRole('User');

        $this->actingAs($user)->get('/users')->assertForbidden();
        $this->actingAs($user)->post('/users', [])->assertForbidden();
    }

    public function test_admin_can_create_user_with_roles(): void
    {
        $this->actingAs($this->admin)
            ->from('/users')
            ->post('/users', [
                'name' => 'New Person',
                'email' => 'new@example.com',
                'password' => 'Secret-pass-123',
                'password_confirmation' => 'Secret-pass-123',
                'is_active' => true,
                'roles' => ['User'],
            ])
            ->assertRedirect('/users');

        $user = User::where('email', 'new@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('User'));
        $this->assertNotSame('Secret-pass-123', $user->password);
    }

    public function test_create_validates_input(): void
    {
        $this->actingAs($this->admin)
            ->from('/users')
            ->post('/users', ['name' => '', 'email' => 'not-an-email', 'password' => 'a', 'roles' => ['Nope']])
            ->assertSessionHasErrors(['name', 'email', 'password', 'roles.0']);
    }

    public function test_admin_cannot_grant_super_admin_role(): void
    {
        $target = User::factory()->create();

        $this->actingAs($this->admin)
            ->from('/users')
            ->put("/users/{$target->id}", [
                'name' => $target->name,
                'email' => $target->email,
                'is_active' => true,
                'roles' => [User::SUPER_ADMIN_ROLE],
            ])
            ->assertSessionHasErrors('roles');

        $this->assertFalse($target->fresh()->isSuperAdmin());
    }

    public function test_admin_cannot_edit_or_delete_a_super_admin(): void
    {
        $this->actingAs($this->admin)->put("/users/{$this->superAdmin->id}", [
            'name' => 'Hijacked',
            'email' => $this->superAdmin->email,
            'is_active' => true,
        ])->assertForbidden();
        $this->actingAs($this->admin)->delete("/users/{$this->superAdmin->id}")->assertForbidden();
    }

    public function test_admin_can_deactivate_and_delete_regular_users(): void
    {
        $target = User::factory()->create();

        $this->actingAs($this->admin)
            ->from('/users')
            ->put("/users/{$target->id}", [
                'name' => $target->name,
                'email' => $target->email,
                'is_active' => false,
                'roles' => [],
            ])
            ->assertRedirect('/users');
        $this->assertFalse($target->fresh()->is_active);

        $this->actingAs($this->admin)->delete("/users/{$target->id}")->assertRedirect('/users');
        $this->assertModelMissing($target);
    }

    public function test_users_cannot_delete_or_deactivate_themselves(): void
    {
        $this->actingAs($this->admin)
            ->delete("/users/{$this->admin->id}")
            ->assertSessionHas('error');
        $this->assertModelExists($this->admin);

        $this->actingAs($this->admin)
            ->put("/users/{$this->admin->id}", [
                'name' => $this->admin->name,
                'email' => $this->admin->email,
                'is_active' => false,
                'roles' => ['Admin'],
            ])
            ->assertSessionHasErrors('is_active');
    }

    public function test_last_super_admin_keeps_role(): void
    {
        $this->actingAs($this->superAdmin)
            ->put("/users/{$this->superAdmin->id}", [
                'name' => 'Root',
                'email' => $this->superAdmin->email,
                'is_active' => true,
                'roles' => ['Admin'],
            ])
            ->assertSessionHasErrors('roles');

        $this->assertTrue($this->superAdmin->fresh()->isSuperAdmin());
    }

    public function test_super_admin_role_can_move_when_another_exists(): void
    {
        $second = User::factory()->create()->assignRole(User::SUPER_ADMIN_ROLE);

        $this->actingAs($this->superAdmin)
            ->from('/users')
            ->put("/users/{$second->id}", [
                'name' => $second->name,
                'email' => $second->email,
                'is_active' => true,
                'roles' => ['Admin'],
            ])
            ->assertRedirect('/users');

        $this->assertFalse($second->fresh()->isSuperAdmin());
    }
}
