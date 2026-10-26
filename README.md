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

## Tests

```bash
php artisan test
```
