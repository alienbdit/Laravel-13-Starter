# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Environment (Windows-specific, important)

`php` and `composer` are **not on PATH**. Use these explicit paths:

- PHP 8.5: `E:\PHP\php85\php.exe` (required — Laravel 13 needs PHP 8.3+; the XAMPP installs elsewhere on this machine are too old)
- Composer: `& E:\PHP\php85\php.exe E:\PHP\composer.phar <args>`

## Commands

```powershell
# Run the dev server (from the project root)
& E:\PHP\php85\php.exe artisan serve

# Artisan in general
& E:\PHP\php85\php.exe artisan <command>

# Run all tests
& E:\PHP\php85\php.exe artisan test

# Run a single test file / single test method
& E:\PHP\php85\php.exe artisan test tests/Feature/ExampleTest.php
& E:\PHP\php85\php.exe artisan test --filter=test_method_name

# Code style (Laravel Pint)
& E:\PHP\php85\php.exe vendor/bin/pint
```

There is **no asset build step**: no Node, npm, or Vite. Never reintroduce `@vite`, `package.json`, or `npm` commands — this was deliberately removed. All CSS/JS is pre-built and served statically from `public/assets/`.

Database is SQLite (`database/database.sqlite`), Laravel's default config.

## Architecture

Laravel 13 starter using the **Sneat Bootstrap 5 admin template** (ThemeSelection, Pro v1.0.0 — commercially licensed). The original template zip is at `E:\PHP\sneat-1.0.0.zip`; its `html/` folder contains ~40 more static pages (forms, tables, cards, UI components, account settings) that can be converted to Blade the same way the existing pages were.

### View structure

Two Blade layouts, both loading Sneat's assets via `asset()`:

- `resources/views/layouts/app.blade.php` — admin shell (sidebar + navbar + content + footer). Includes the partials below and yields `content`. Pages can inject extra assets with `@push('page-css')` / `@push('page-js')`.
- `resources/views/layouts/auth.blade.php` — centered-card layout for auth screens (no sidebar/navbar, adds `page-auth.css`). Renders the logo/card shell; auth views supply only the form.

Partials in `resources/views/partials/`: `menu.blade.php` (sidebar; contains commented examples for adding menu items/submenus/headers — active state is driven by `request()->is(...)`), `navbar.blade.php`, `footer.blade.php`, `brand-logo.blade.php` (shared SVG, used by both layouts).

Pages: `dashboard.blade.php` (route `/`), `auth/` (login, register, forgot/reset password), and `account-settings/` (account, notifications, connections — under `/settings/*`, demo forms without backends except the prefilled name/email). The auth forms have `@csrf`, `old()` repopulation, and `@error` blocks wired for Bootstrap's `is-invalid` styling.

### Sneat template conventions

- The `<html>` tag carries `data-assets-path="{{ asset('assets') }}/"` — Sneat's `main.js` uses it to resolve images (e.g. light/dark illustration swaps). Keep it on any new layout.
- `assets/vendor/js/helpers.js` and `assets/js/config.js` must load in `<head>` after core CSS; jQuery/Popper/`bootstrap.js`/`menu.js`/`main.js` load at the end of `<body>`. Sneat ships its own Bootstrap build inside these — don't add a separate Bootstrap.
- Theme colors are defined in `assets/vendor/css/theme-default.css` and mirrored in `assets/js/config.js`.

### Authentication

Session-based auth lives in `app/Http/Controllers/AuthController.php`. The dashboard (`/`) and `POST /logout` are behind the `auth` middleware; login/register/password-reset routes are behind `guest`. Login accepts email **or** username (the `users.name` column doubles as the username and is validated unique on registration). Passwords hash automatically via the User model's `hashed` cast.

Password reset uses Laravel's built-in broker with the conventional route names (`password.request`, `password.email`, `password.reset`, `password.update`) — the framework's `ResetPassword` notification builds its link from `password.reset`, so don't rename it. `MAIL_MAILER=log` in `.env`, so reset emails land in `storage/logs/laravel.log` during development.

`tests/Feature/AuthTest.php` and `tests/Feature/PasswordResetTest.php` cover the full flow — run them after touching auth.

### Adding a page

1. Create a view with `@extends('layouts.app')` + `@section('content', ...)`; put page-specific scripts in `@push('page-js')`.
2. Add a route in `routes/web.php`.
3. Copy a commented menu-item example in `partials/menu.blade.php` and point it at the route.

## RBAC (Role-Based Access Control)

Built from scratch — no third-party package. Four tables: `roles`, `permissions`, `role_user`, `permission_role`.

### Default roles

| Role | What it has |
|---|---|
| `super_admin` | Bypasses all Gate checks (`Gate::before`) — full access |
| `admin` | All user + role view permissions |
| `editor` | `users.view` only |
| `viewer` | `users.view` only |

### Key files

- `app/Models/Role.php` — `permissions()`, `users()`, `hasPermission(string)`
- `app/Models/Permission.php` — `roles()`
- `app/Models/User.php` — `roles()`, `hasRole(string|array)`, `hasPermission(string)`
- `app/Http/Middleware/RequireRole.php` — `role:admin,editor` middleware alias
- `app/Http/Middleware/RequirePermission.php` — `permission:posts.view` middleware alias
- `app/Providers/AppServiceProvider.php` — defines all DB permissions as Gate abilities; `super_admin` bypasses via `Gate::before`
- `database/seeders/RolePermissionSeeder.php` — seeds permissions and assigns them to roles

### Adding a new module (e.g. Posts)

**1. Add permissions to `RolePermissionSeeder.php`:**
```php
['name' => 'posts.view',   'label' => 'View Posts'],
['name' => 'posts.create', 'label' => 'Create Posts'],
['name' => 'posts.edit',   'label' => 'Edit Posts'],
['name' => 'posts.delete', 'label' => 'Delete Posts'],
```
Also add them to the relevant role sync blocks, then re-run:
```powershell
& E:\PHP\php85\php.exe artisan db:seed --class=RolePermissionSeeder
```

**2. Protect routes in `routes/web.php`:**
```php
Route::middleware('permission:posts.view')->group(function () {
    Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
});
Route::middleware('permission:posts.create')->group(function () {
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
});
Route::middleware('permission:posts.edit')->group(function () {
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
});
Route::delete('/posts/{post}', [PostController::class, 'destroy'])
    ->name('posts.destroy')->middleware('permission:posts.delete');
```

**3. Guard buttons in Blade with `@can`:**
```blade
@can('posts.create')
    <a href="{{ route('posts.create') }}" class="btn btn-primary">New Post</a>
@endcan

@can('posts.delete')
    <form ...>@csrf @method('DELETE')<button>Delete</button></form>
@endcan
```

**4. Add the menu item to `partials/menu.blade.php`:**
```blade
@can('posts.view')
<li class="menu-item {{ request()->is('posts*') ? 'active' : '' }}">
    <a href="{{ route('posts.index') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-file"></i>
        <div>Posts</div>
    </a>
</li>
@endcan
```

**5. Optionally double-check in the controller:**
```php
Gate::authorize('posts.create'); // throws 403 if denied
// or
abort_unless(auth()->user()->hasPermission('posts.delete'), 403);
```

### Admin panel

- `/admin/users` — manage user role assignments (requires `super_admin` or `admin`)
- `/admin/roles` — CRUD roles and their permissions (create/edit/delete requires `super_admin`)

### Production note

Run only `RolePermissionSeeder` in production — `DatabaseSeeder` also creates a `test@example.com` dev account.
