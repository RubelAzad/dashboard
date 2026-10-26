<?php

namespace App\Listeners;

use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

/**
 * Records sign-in, sign-out, failed sign-in and lockout events in the activity log.
 * Registered through Laravel's listener discovery (each handle* method's type hint).
 */
class LogAuthenticationActivity
{
    public function handleLogin(Login $event): void
    {
        $user = $event->user instanceof User ? $event->user : null;

        ActivityLogger::activity(
            'login',
            $event->remember ? 'Signed in (remembered session)' : 'Signed in',
            $user,
        );
    }

    public function handleLogout(Logout $event): void
    {
        if ($event->user instanceof User) {
            ActivityLogger::activity('logout', 'Signed out', $event->user);
        }
    }

    public function handleFailed(Failed $event): void
    {
        $email = (string) ($event->credentials['email'] ?? '');

        // The causer stays empty: the person attempting may not be the account owner.
        ActivityLogger::activity('login_failed', "Failed sign-in attempt for {$email}", null, ['email' => $email]);
    }

    public function handleLockout(Lockout $event): void
    {
        $email = (string) $event->request->input('email');

        ActivityLogger::activity('lockout', "Too many sign-in attempts for {$email}", null, ['email' => $email]);
    }
}
