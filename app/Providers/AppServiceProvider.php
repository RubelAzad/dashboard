<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Super Admin implicitly holds every permission (checked via can()/@can/permission middleware).
        Gate::before(fn (User $user) => $user->hasRole(User::SUPER_ADMIN_ROLE) ? true : null);
    }
}
