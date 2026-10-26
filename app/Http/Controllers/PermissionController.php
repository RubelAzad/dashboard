<?php

namespace App\Http\Controllers;

use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return static::permissionMiddleware('permissions');
    }

    public function index(): Response
    {
        return Inertia::render('Permissions/Index', [
            'permissionGroups' => Permissions::grouped(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $permission = Permission::create([
            'name' => $this->validateName($request),
            'guard_name' => 'web',
        ]);

        return back()->with('success', "Permission {$permission->name} created.");
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        if (Permissions::isCore($permission->name)) {
            return back()->with('error', 'Core permissions are used by the application and cannot be renamed.');
        }

        $permission->update(['name' => $this->validateName($request, $permission)]);

        return back()->with('success', "Permission {$permission->name} updated.");
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        if (Permissions::isCore($permission->name)) {
            return back()->with('error', 'Core permissions are used by the application and cannot be deleted.');
        }

        $permission->delete();

        return back()->with('success', "Permission {$permission->name} deleted.");
    }

    private function validateName(Request $request, ?Permission $ignore = null): string
    {
        $request->merge(['name' => strtolower(trim((string) $request->input('name')))]);

        return $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                'regex:/^[a-z0-9_-]+\.[a-z0-9_-]+$/',
                Rule::unique('permissions', 'name')->where('guard_name', 'web')->ignore($ignore),
            ],
        ], [
            'name.regex' => 'Use the format module.action, e.g. reports.export.',
        ])['name'];
    }
}
