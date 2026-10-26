<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Only a Super Admin may modify a Super Admin account; Super Admins bypass
     * this check entirely via Gate::before. Route middleware already ensures the
     * actor holds the relevant users.* permission.
     */
    public function manage(User $actor, User $target): bool
    {
        return ! $target->isSuperAdmin();
    }
}
