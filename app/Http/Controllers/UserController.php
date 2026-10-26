<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return static::permissionMiddleware('users');
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        $users = User::query()
            ->with('roles:id,name')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn (User $user) => $this->present($user));

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Users/Create', [
            'roles' => $this->assignableRoles($request->user()),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $user = User::create([
            ...$request->safe()->only('name', 'email', 'password'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $user->syncRoles($request->validated('roles', []));

        return to_route('users.index')->with('success', "User {$user->name} created.");
    }

    public function show(User $user): Response
    {
        $user->load('roles:id,name');

        return Inertia::render('Users/Show', [
            'user' => [
                ...$this->present($user),
                'updated_at' => Setting::localTime($user->updated_at),
                'permissions' => $user->getAllPermissions()->pluck('name')->sort()->values(),
            ],
        ]);
    }

    public function edit(Request $request, User $user): Response
    {
        Gate::authorize('manage', $user);

        $user->load('roles:id,name');

        return Inertia::render('Users/Edit', [
            'user' => $this->present($user),
            'roles' => $this->assignableRoles($request->user()),
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('manage', $user);

        $roles = $request->validated('roles', []);
        $isActive = $request->boolean('is_active');

        if ($user->is($request->user()) && ! $isActive) {
            throw ValidationException::withMessages(['is_active' => 'You cannot deactivate your own account.']);
        }

        if ($user->isLastActiveSuperAdmin() && (! $isActive || ! in_array(User::SUPER_ADMIN_ROLE, $roles, true))) {
            throw ValidationException::withMessages(['roles' => 'This is the last active Super Admin; keep the role and active status.']);
        }

        $user->fill([...$request->safe()->only('name', 'email'), 'is_active' => $isActive]);

        if ($request->filled('password')) {
            $user->password = $request->validated('password');
        }

        $user->save();
        $user->syncRoles($roles);

        return to_route('users.index')->with('success', "User {$user->name} updated.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('manage', $user);

        if ($user->is($request->user())) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->isLastActiveSuperAdmin()) {
            return back()->with('error', 'The last active Super Admin cannot be deleted.');
        }

        $user->delete();

        return to_route('users.index')->with('success', "User {$user->name} deleted.");
    }

    /**
     * @return array<string, mixed>
     */
    private function present(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_active' => $user->is_active,
            'roles' => $user->roles->pluck('name'),
            'created_at' => Setting::localTime($user->created_at),
        ];
    }

    /**
     * Roles the current user may assign; only Super Admins can grant Super Admin.
     *
     * @return list<string>
     */
    private function assignableRoles(User $actor): array
    {
        return Role::query()
            ->when(! $actor->isSuperAdmin(), fn ($query) => $query->where('name', '!=', User::SUPER_ADMIN_ROLE))
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }
}
