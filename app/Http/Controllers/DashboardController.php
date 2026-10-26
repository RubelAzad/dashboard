<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Dashboard', [
            'stats' => [
                'users' => User::count(),
                'activeUsers' => User::where('is_active', true)->count(),
                'roles' => Role::count(),
                'permissions' => Permission::count(),
            ],
            'roleBreakdown' => Role::query()
                ->withCount('users')
                ->orderByDesc('users_count')
                ->get(['id', 'name'])
                ->map(fn (Role $role) => ['name' => $role->name, 'users' => $role->users_count]),
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
            // Only sent to users who may read the activity log.
            'recentActivity' => $request->user()->can('activity-logs.view')
                ? ActivityLog::latest('created_at')->latest('id')
                    ->limit(6)
                    ->get(['id', 'log_name', 'event', 'description', 'causer_name', 'created_at'])
                    ->map(fn (ActivityLog $log) => [
                        'id' => $log->id,
                        'log_name' => $log->log_name,
                        'event' => $log->event,
                        'description' => $log->description,
                        'causer' => $log->causer_name,
                        'created_at' => Setting::localTime($log->created_at),
                        'created_at_iso' => $log->created_at?->toIso8601String(),
                    ])
                : null,
        ]);
    }
}
