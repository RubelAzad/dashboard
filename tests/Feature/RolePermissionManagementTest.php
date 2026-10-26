<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->superAdmin = User::factory()->create()->assignRole(User::SUPER_ADMIN_ROLE);
    }

    public function test_roles_index_lists_roles(): void
    {
        $this->actingAs($this->superAdmin)
            ->get('/roles')
            ->assertInertia(fn (Assert $page) => $page->component('Roles/Index')->has('roles', 3));
    }

    public function test_role_can_be_created_with_permissions(): void
    {
        $this->actingAs($this->superAdmin)
            ->from('/roles')
            ->post('/roles', ['name' => 'Editor', 'permissions' => ['users.view', 'dashboard.view']])
            ->assertRedirect('/roles');

        $role = Role::findByName('Editor');
        $this->assertTrue($role->hasPermissionTo('users.view'));
        $this->assertFalse($role->hasPermissionTo('users.delete'));
    }

    public function test_role_permissions_are_enforced(): void
    {
        $role = Role::create(['name' => 'Viewer']);
        $role->syncPermissions(['users.view']);
        $user = User::factory()->create()->assignRole('Viewer');

        $this->actingAs($user)->get('/users')->assertOk();
        $this->actingAs($user)->post('/users', ['name' => 'X', 'email' => 'x@example.com'])->assertForbidden();
    }

    public function test_super_admin_role_cannot_be_edited_or_deleted(): void
    {
        $role = Role::findByName(User::SUPER_ADMIN_ROLE);

        $this->actingAs($this->superAdmin)->put("/roles/{$role->id}", ['name' => 'Renamed'])->assertSessionHas('error');
        $this->actingAs($this->superAdmin)->delete("/roles/{$role->id}")->assertSessionHas('error');

        $this->assertModelExists($role);
        $this->assertSame(User::SUPER_ADMIN_ROLE, $role->fresh()->name);
    }

    public function test_admin_role_cannot_manage_permissions(): void
    {
        $admin = User::factory()->create()->assignRole('Admin');

        $this->actingAs($admin)->get('/permissions')->assertForbidden();
        $this->actingAs($admin)->post('/permissions', ['name' => 'reports.view'])->assertForbidden();
    }

    public function test_permission_lifecycle(): void
    {
        $this->actingAs($this->superAdmin)
            ->post('/permissions', ['name' => 'Reports.Export'])
            ->assertSessionHasNoErrors();
        $permission = Permission::findByName('reports.export');

        $this->actingAs($this->superAdmin)
            ->put("/permissions/{$permission->id}", ['name' => 'reports.download'])
            ->assertSessionHasNoErrors();
        $this->assertSame('reports.download', $permission->fresh()->name);

        $this->actingAs($this->superAdmin)->delete("/permissions/{$permission->id}");
        $this->assertModelMissing($permission);
    }

    public function test_permission_name_format_is_validated(): void
    {
        $this->actingAs($this->superAdmin)
            ->post('/permissions', ['name' => 'no spaces allowed'])
            ->assertSessionHasErrors('name');
    }

    public function test_core_permissions_cannot_be_deleted_or_renamed(): void
    {
        $permission = Permission::findByName('users.view');

        $this->actingAs($this->superAdmin)->delete("/permissions/{$permission->id}")->assertSessionHas('error');
        $this->actingAs($this->superAdmin)->put("/permissions/{$permission->id}", ['name' => 'users.list'])->assertSessionHas('error');

        $this->assertSame('users.view', $permission->fresh()->name);
    }

    public function test_permissions_are_grouped_by_module(): void
    {
        $this->actingAs($this->superAdmin)
            ->get('/permissions')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Permissions/Index')
                ->has('permissionGroups.users', 4)
                ->has('permissionGroups.settings', 2));
    }
}
