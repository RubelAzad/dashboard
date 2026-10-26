<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controllers\Middleware;

abstract class Controller
{
    /**
     * Map resource actions to "<module>.<action>" permission middleware.
     *
     * @return list<Middleware>
     */
    protected static function permissionMiddleware(string $module): array
    {
        return [
            new Middleware("permission:{$module}.view", only: ['index', 'show']),
            new Middleware("permission:{$module}.create", only: ['create', 'store']),
            new Middleware("permission:{$module}.update", only: ['edit', 'update']),
            new Middleware("permission:{$module}.delete", only: ['destroy']),
        ];
    }
}
