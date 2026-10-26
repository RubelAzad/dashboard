# Laravel + Inertia.js + Svelte Dashboard Project

## 1. Project Overview

Build a basic, reusable admin dashboard using Laravel, Inertia.js, Svelte 5, JavaScript, Tailwind CSS, and MySQL.

**Frontend restrictions**
- Do not use React.
- Do not use Vue or Vue 3.
- Do not build dashboard pages with Blade templates.
- Use Svelte components for frontend pages, connected to Laravel through Inertia.js.
- Laravel may use the minimal root HTML view required to mount Inertia; this is not a Blade-based dashboard UI.

## 2. Technology Stack

| Requirement | Recommended choice |
|---|---|
| Backend | Laravel 12 |
| Frontend | Svelte 5 |
| Page integration | Inertia.js |
| Language | JavaScript |
| Styling | Tailwind CSS |
| Database | MySQL |
| Roles and permissions | Spatie Laravel Permission |

## 3. Core Modules

### Authentication
- Login
- Logout
- Authenticated session handling
- Protect private pages with authentication middleware
- Validation and useful error messages

### Dashboard
- Dashboard overview
- Summary/statistic cards
- Recent activity or recent users section (if activity data is implemented)
- Responsive layout

### User Management
- List users with pagination
- Search users
- Create, view, edit, and delete users
- Activate/deactivate users if required
- Assign roles to users
- Validate all submitted data on the server

### Role Management
- List, create, edit, and delete roles
- Assign permissions to roles
- Prevent accidental removal of the last Super Admin role assignment

### Permission Management
- List and create permissions
- Edit/delete permissions when appropriate
- Group permissions by module, for example Users, Roles, Permissions, and Settings
- Enforce authorization on the server, not only by hiding UI elements

### Settings
- Application name
- Logo or branding settings (optional upload)
- Timezone
- Basic general settings stored in the database when appropriate
- Validate and authorize setting updates

## 4. Suggested Frontend Structure

```text
resources/
└── js/
    ├── components/
    ├── layouts/
    │   └── AdminLayout.svelte
    ├── pages/
    │   ├── Dashboard.svelte
    │   ├── Users/
    │   │   ├── Index.svelte
    │   │   ├── Create.svelte
    │   │   ├── Edit.svelte
    │   │   └── Show.svelte
    │   ├── Roles/
    │   │   ├── Index.svelte
    │   │   ├── Create.svelte
    │   │   └── Edit.svelte
    │   ├── Permissions/
    │   │   └── Index.svelte
    │   └── Settings/
    │       └── Index.svelte
    └── types/
```

Suggested Laravel structure:

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php
│   │   ├── UserController.php
│   │   ├── RoleController.php
│   │   ├── PermissionController.php
│   │   └── SettingController.php
│   └── Requests/
├── Models/
└── Policies/

database/
├── migrations/
└── seeders/

routes/
└── web.php
```

The exact file layout can vary by the Laravel starter kit and installed Inertia Svelte adapter.

## 5. Database Setup

Create a MySQL database, for example:

```sql
CREATE DATABASE basic_admin
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
```

Configure `.env`:

```dotenv
APP_NAME="Basic Admin"
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=basic_admin
DB_USERNAME=root
DB_PASSWORD=
```

Change the database username and password to match your local MySQL configuration.

## 6. Setup Notes

1. Install a Laravel version compatible with the chosen PHP version.
2. Install/configure the Inertia.js Svelte adapter and Svelte 5 build setup.
3. Configure Tailwind CSS and Vite.
4. Configure Laravel authentication using a compatible authentication solution.
5. Install Spatie Laravel Permission and publish its configuration/migrations.
6. Create the application database and configure `.env`.
7. Run migrations.
8. Seed initial roles and permissions, and create an initial administrator securely.
9. Build the Svelte pages and shared admin layout.
10. Protect routes and enforce authorization in Laravel middleware, policies, or controllers.
11. Test login/logout, CRUD operations, role assignments, permissions, validation, and responsive layouts.

Do not assume that the standard React or Vue starter kit automatically provides Svelte support. Confirm the current Inertia Svelte adapter and starter-kit setup instructions before installing dependencies.

## 7. Suggested Route Plan

| Method | URL | Purpose |
|---|---|---|
| GET | `/dashboard` | Dashboard |
| GET | `/users` | List users |
| GET/POST | `/users/create` | Show and submit user creation form (or use conventional resource routes) |
| GET/PATCH/DELETE | `/users/{user}` | Show/update/delete a user using conventional resource routes |
| Resource routes | `/roles` | Role management |
| Resource routes | `/permissions` | Permission management |
| GET/PUT | `/settings` | View/update settings |
| POST | `/logout` | Logout |

Use Laravel resource routes where suitable and follow the authentication conventions of the chosen starter kit.

## 8. Security Requirements

- Hash passwords using Laravel's supported password hashing.
- Validate all inputs on the server.
- Use authentication middleware for private routes.
- Use Spatie permissions or Laravel policies/middleware to authorize sensitive actions.
- Never rely solely on frontend menu visibility for access control.
- Restrict role/permission changes to authorized administrators.
- Protect uploads by validating file type and size.
- Use CSRF protection and secure session defaults.
- Do not commit `.env` or credentials to source control.

## 9. Acceptance Checklist

- [ ] Login and logout work.
- [ ] Unauthenticated users cannot access the dashboard.
- [ ] Dashboard loads through Inertia and Svelte.
- [ ] User CRUD, search, and pagination work.
- [ ] Roles can be created and assigned.
- [ ] Permissions can be managed and enforced server-side.
- [ ] Settings can be viewed and updated by authorized users.
- [ ] UI is responsive and styled with Tailwind CSS.
- [ ] MySQL migrations and seeders run successfully.
- [ ] No React or Vue is used.
- [ ] No Blade templates are used to build dashboard pages.

## 10. Official Documentation

- Laravel: https://laravel.com/docs
- Inertia.js: https://inertiajs.com/
- Inertia Svelte adapter: https://inertiajs.com/client-side-setup
- Svelte: https://svelte.dev/docs
- Tailwind CSS: https://tailwindcss.com/docs
- Spatie Laravel Permission: https://spatie.be/docs/laravel-permission
