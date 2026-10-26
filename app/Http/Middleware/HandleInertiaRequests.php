<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use App\Support\Appearance;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Middleware;
use Symfony\Component\HttpFoundation\Response;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Encrypt browser history (so Inertia::clearHistory() on logout wipes cached pages) only over
     * HTTPS: browsers expose the Web Crypto API to secure pages only, and on plain HTTP Inertia
     * throws "Unable to encrypt history" and stops rendering.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isSecure()) {
            Inertia::encryptHistory();
        }

        return parent::handle($request, $next);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'app' => fn () => [
                'name' => Setting::get('app_name', config('app.name')),
                'logo' => ($logo = Setting::get('logo_path')) ? Storage::disk('public')->url($logo) : null,
            ],
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                ] : null,
            ],
            // Theme: organization default, the user's own override (if any) and whether overrides are allowed.
            'theme' => fn () => [
                'defaults' => Appearance::defaults(),
                'mine' => Appearance::forUser($user),
                'allowCustom' => Appearance::allowUserThemes(),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
