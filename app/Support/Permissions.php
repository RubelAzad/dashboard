<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

final class Permissions
{
    /**
     * Core permissions grouped by module. Names follow "<module>.<action>".
     * Routes depend on these, so they cannot be renamed or deleted from the UI.
     *
     * @var array<string, list<string>>
     */
    public const CORE = [
        'dashboard' => ['view'],
        'users' => ['view', 'create', 'update', 'delete'],
        'roles' => ['view', 'create', 'update', 'delete'],
        'permissions' => ['view', 'create', 'update', 'delete'],
        'settings' => ['view', 'update'],
        'appearance' => ['update'],
        'activity-logs' => ['view'],
        'audit-logs' => ['view'],
    ];

    /**
     * @return list<string>
     */
    public static function coreNames(): array
    {
        return collect(self::CORE)
            ->flatMap(fn (array $actions, string $module) => array_map(fn ($action) => "{$module}.{$action}", $actions))
            ->values()
            ->all();
    }

    public static function isCore(string $name): bool
    {
        return in_array($name, self::coreNames(), true);
    }

    /**
     * All permissions grouped by module, e.g. ['users' => [['id' => 1, 'name' => 'users.view', ...]]].
     *
     * @return Collection<string, Collection<int, array<string, mixed>>>
     */
    public static function grouped(): Collection
    {
        return Permission::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Permission $permission) => [
                'id' => $permission->id,
                'name' => $permission->name,
                'module' => str($permission->name)->before('.')->toString(),
                'action' => str($permission->name)->after('.')->toString(),
                'core' => self::isCore($permission->name),
            ])
            ->groupBy('module');
    }
}
