<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->admin = User::factory()->create(['name' => 'Ada Admin'])->assignRole('Admin');
    }

    public function test_sign_in_sign_out_and_failed_attempts_are_logged(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->post('/logout');

        $this->assertSame(
            ['login_failed', 'login', 'logout'],
            ActivityLog::orderBy('id')->pluck('event')->all(),
        );
        $this->assertNull(ActivityLog::where('event', 'login_failed')->value('causer_id'));
        $this->assertSame($user->id, ActivityLog::where('event', 'login')->value('causer_id'));
    }

    public function test_user_update_records_only_changed_fields_and_hides_passwords(): void
    {
        $user = User::factory()->create(['name' => 'Old Name'])->assignRole('User');

        $this->actingAs($this->admin)->put("/users/{$user->id}", [
            'name' => 'New Name',
            'email' => $user->email,
            'password' => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
            'is_active' => true,
            'roles' => ['Admin'],
        ])->assertSessionHasNoErrors();

        $log = ActivityLog::where('log_name', 'audit')->sole();

        $this->assertSame('updated', $log->event);
        $this->assertSame('Updated user New Name', $log->description);
        $this->assertSame($this->admin->id, $log->causer_id);
        $this->assertSame(['name' => 'Old Name', 'roles' => ['User']], $log->properties['old']);
        $this->assertSame(['name' => 'New Name', 'roles' => ['Admin'], 'password' => '(changed)'], $log->properties['new']);
        $this->assertStringNotContainsString('brand-new-password', json_encode($log->properties));
    }

    public function test_unchanged_update_is_not_logged(): void
    {
        $role = Role::findByName('User');

        $this->actingAs($this->admin)->put("/roles/{$role->id}", [
            'name' => 'User',
            'permissions' => ['dashboard.view'],
        ]);

        $this->assertSame(0, ActivityLog::where('log_name', 'audit')->count());
    }

    public function test_role_permission_changes_are_audited(): void
    {
        $role = Role::findByName('User');

        $this->actingAs($this->admin)->put("/roles/{$role->id}", [
            'name' => 'User',
            'permissions' => ['dashboard.view', 'users.view'],
        ]);

        $log = ActivityLog::where('log_name', 'audit')->sole();
        $this->assertSame(['permissions' => ['dashboard.view']], $log->properties['old']);
        $this->assertSame(['permissions' => ['dashboard.view', 'users.view']], $log->properties['new']);
    }

    public function test_log_pages_require_permission(): void
    {
        $user = User::factory()->create()->assignRole('User');

        $this->actingAs($user)->get('/logs/activity')->assertForbidden();
        $this->actingAs($user)->get('/logs/audit')->assertForbidden();
    }

    public function test_activity_page_lists_all_entries_and_filters_by_type(): void
    {
        $this->actingAs($this->admin)->post('/users', [
            'name' => 'Created Person',
            'email' => 'created@example.com',
            'password' => 'password-123',
            'password_confirmation' => 'password-123',
            'is_active' => true,
        ]);
        auth()->logout(); // Adds a "logout" activity entry.
        $this->actingAs($this->admin);

        $this->get('/logs/activity')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Logs/Activity')
                ->has('logs.data', 2)
            );

        $this->get('/logs/activity?type=audit')
            ->assertInertia(fn (Assert $page) => $page
                ->has('logs.data', 1)
                ->where('logs.data.0.description', 'Created user Created Person')
            );
    }

    public function test_audit_page_only_shows_data_changes_and_filters_by_subject(): void
    {
        $this->actingAs($this->admin)->post('/roles', ['name' => 'Editor', 'permissions' => ['users.view']]);
        $this->put('/settings', ['app_name' => 'Renamed', 'timezone' => 'UTC']);
        auth()->logout();
        $this->actingAs($this->admin);

        $this->get('/logs/audit')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Logs/Audit')
                ->has('logs.data', 2)
                ->where('subjects', ['role', 'settings'])
            );

        $this->get('/logs/audit?subject=role&search=editor')
            ->assertInertia(fn (Assert $page) => $page
                ->has('logs.data', 1)
                ->where('logs.data.0.properties.new.name', 'Editor')
            );
    }

    public function test_dashboard_only_includes_recent_activity_for_permitted_users(): void
    {
        $this->actingAs($this->admin)->get('/dashboard')
            ->assertInertia(fn (Assert $page) => $page->has('recentActivity'));

        $this->actingAs(User::factory()->create()->assignRole('User'))->get('/dashboard')
            ->assertInertia(fn (Assert $page) => $page->where('recentActivity', null));
    }

    public function test_old_entries_are_pruned(): void
    {
        ActivityLog::create(['log_name' => 'activity', 'event' => 'login', 'description' => 'old', 'created_at' => now()->subDays(ActivityLog::RETENTION_DAYS + 1)]);
        ActivityLog::create(['log_name' => 'activity', 'event' => 'login', 'description' => 'new']);

        $this->artisan('model:prune', ['--model' => ActivityLog::class]);

        $this->assertSame(['new'], ActivityLog::pluck('description')->all());
    }
}
