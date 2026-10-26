<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Dashboard', [
            'stats' => [
                'users' => User::count(),
                'activeUsers' => User::where('is_active', true)->count(),
                'roles' => Role::count(),
                'permissions' => Permission::count(),
            ],
            'recentUsers' => User::latest()
                ->limit(5)
                ->get(['id', 'name', 'email', 'is_active', 'created_at'])
                ->map(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_active' => $user->is_active,
                    'created_at' => Setting::localTime($user->created_at),
                ]),
        ]);
    }
}
