<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Models\User;
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
            ->withCount(['users', 'permissions'])
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'users_count' => $role->users_count,
                'permissions_count' => $role->permissions_count,
                'protected' => $role->name === User::SUPER_ADMIN_ROLE,
            ]);

        return Inertia::render('Roles/Index', ['roles' => $roles]);
    }

    public function create(): Response
    {
        return Inertia::render('Roles/Create', [
            'permissionGroups' => Permissions::grouped(),
        ]);
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        $role = Role::create(['name' => $request->validated('name'), 'guard_name' => 'web']);
        $role->syncPermissions($request->validated('permissions', []));

        return to_route('roles.index')->with('success', "Role {$role->name} created.");
    }

    public function edit(Role $role): Response|RedirectResponse
    {
        if ($role->name === User::SUPER_ADMIN_ROLE) {
            return to_route('roles.index')->with('error', 'The Super Admin role always has every permission and cannot be edited.');
        }

        return Inertia::render('Roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions()->pluck('name'),
            ],
            'permissionGroups' => Permissions::grouped(),
        ]);
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        if ($role->name === User::SUPER_ADMIN_ROLE) {
            return to_route('roles.index')->with('error', 'The Super Admin role cannot be edited.');
        }

        $role->update(['name' => $request->validated('name')]);
        $role->syncPermissions($request->validated('permissions', []));

        return to_route('roles.index')->with('success', "Role {$role->name} updated.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->name === User::SUPER_ADMIN_ROLE) {
            return back()->with('error', 'The Super Admin role cannot be deleted.');
        }

        $role->delete();

        return to_route('roles.index')->with('success', "Role {$role->name} deleted.");
    }
}
