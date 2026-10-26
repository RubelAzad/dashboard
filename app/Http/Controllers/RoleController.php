<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Models\User;
use App\Support\ActivityLogger;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class RoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return static::permissionMiddleware('roles');
    }

    public function index(): Response
    {
        $roles = Role::query()
            ->with('permissions:id,name')
            ->withCount(['users', 'permissions'])
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'users_count' => $role->users_count,
                'permissions_count' => $role->permissions_count,
                'permissions' => $role->permissions->pluck('name'),
                'protected' => $role->name === User::SUPER_ADMIN_ROLE,
            ]);

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'permissionGroups' => Permissions::grouped(),
        ]);
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        $role = Role::create(['name' => $request->validated('name'), 'guard_name' => 'web']);
        $role->syncPermissions($request->validated('permissions', []));

        ActivityLogger::audit('created', 'role', $role->id, $role->name, [], $this->snapshot($role));

        return back()->with('success', "Role {$role->name} created.");
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        if ($role->name === User::SUPER_ADMIN_ROLE) {
            return to_route('roles.index')->with('error', 'The Super Admin role cannot be edited.');
        }

        $before = $this->snapshot($role);

        $role->update(['name' => $request->validated('name')]);
        $role->syncPermissions($request->validated('permissions', []));

        ActivityLogger::audit('updated', 'role', $role->id, $role->name, $before, $this->snapshot($role));

        return back()->with('success', "Role {$role->name} updated.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->name === User::SUPER_ADMIN_ROLE) {
            return back()->with('error', 'The Super Admin role cannot be deleted.');
        }

        $snapshot = $this->snapshot($role);
        $role->delete();

        ActivityLogger::audit('deleted', 'role', $role->id, $role->name, $snapshot);

        return to_route('roles.index')->with('success', "Role {$role->name} deleted.");
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(Role $role): array
    {
        return [
            'name' => $role->name,
            'permissions' => $role->permissions()->pluck('name')->sort()->values()->all(),
        ];
    }
}
