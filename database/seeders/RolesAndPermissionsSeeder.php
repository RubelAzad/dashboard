<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Permissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Permissions::coreNames() as $name) {
            Permission::findOrCreate($name, 'web');
        }

        // Super Admin is granted everything through Gate::before in AppServiceProvider.
        Role::findOrCreate(User::SUPER_ADMIN_ROLE, 'web');

        // Admins manage everything except permissions and the organization theme.
        Role::findOrCreate('Admin', 'web')->syncPermissions(
            Permission::where('name', 'not like', 'permissions.%')
                ->where('name', 'not like', 'appearance.%')
                ->get()
        );

        Role::findOrCreate('User', 'web')->syncPermissions(['dashboard.view']);
    }
}
