# Basic Admin Dashboard

Laravel 12 + Inertia.js + Svelte 5 + Tailwind CSS 4 + MySQL, with roles/permissions via Spatie Laravel Permission. See [document.md](document.md) for the full spec.

## Setup

```bash
composer install
npm install
cp .env.example .env        # then set DB_* and ADMIN_* values
php artisan key:generate
```

Create the database (`basic_admin`, utf8mb4), then:

```bash
php artisan migrate --seed  # prints the admin password if ADMIN_PASSWORD is empty
npm run dev                 # or: npm run build
```

With Laravel Herd the site is served at http://dashboard.test.

## Conventions

- Pages live in `resources/js/pages` and are rendered with `Inertia::render('Name')`.
- Permissions are named `<module>.<action>` (see `RolesAndPermissionsSeeder`). Protect routes with
  `->middleware('permission:users.view')`; `can()` in `resources/js/lib/permissions.js` only hides UI.
- The `Super Admin` role passes every permission check (`Gate::before` in `AppServiceProvider`).
- App settings are key/value rows read through `App\Models\Setting::get()`.

- UI building blocks live in `resources/js/components` (`PageHeader`, `Badge`, `Avatar`, `EmptyState`, `Toggle`, ...).
  Brand colours are the `--color-brand-*` tokens in `resources/css/app.css`; change them to re-theme everything.
- Use `confirm()` from `resources/js/lib/confirm.svelte.js` instead of `window.confirm()` for destructive actions.
- Every signed-in user can edit their own details and password at `/profile`; no permission is required.
- 403/404 (and 500/503 when `APP_DEBUG=false`) render the Svelte `Error` page via `bootstrap/app.php`.
- **Logs.** Sign-in/out and failed attempts are logged by `App\Listeners\LogAuthenticationActivity`. Data changes are
  logged explicitly with `ActivityLogger::audit()` in controllers, so add a call when you add a new editable resource.
  Pages: `/logs/activity` (`activity-logs.view`) and `/logs/audit` (`audit-logs.view`). Rows older than
  `ActivityLog::RETENTION_DAYS` are removed by `model:prune`, so run the scheduler (`php artisan schedule:work`).
- **Back button after logout.** Pages are sent with `Cache-Control: no-store`, the client re-checks the session on every
  Back/Forward step, and logout clears Inertia's history. History encryption only works over HTTPS (`herd secure dashboard`).
- **Themes.** Options (design, accent, neutral, font, icons, radius, density) are listed in `App\Support\Appearance`;
  each becomes a `data-*` attribute on `<html>` and `resources/css/app.css` maps it to CSS variables. To add a value,
  add it there, in the CSS, and in `resources/js/lib/appearance.js` (picker labels). Super Admins (`appearance.update`)
  set the default in Settings; users override it from the header palette button unless that is switched off.

## Tests

```bash
php artisan test
```
