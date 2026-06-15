# Laravel Starter

A production-ready **Laravel 13** starter application built on the [Sneat Bootstrap 5 Admin Template](https://themeselection.com/item/sneat-bootstrap-html-admin-template/). Ships with authentication, role-based access control, two-factor authentication, profile management, site settings, and a browser-based artisan console — all wired up and ready to extend.

## Features

- **Authentication** — login by email or username, registration (admin-toggleable), password reset, remember-me
- **Two-Factor Authentication** — TOTP (Google Authenticator / Authy) with QR code setup; SMS fallback; site-wide 2FA enforcement toggle
- **Role-Based Access Control** — custom implementation, no third-party package; `Gate`, `@can`, and middleware guards throughout
- **Profile Management** — display name, email, password change, avatar upload
- **Admin Panel**
  - User management with role assignment
  - Role & permission CRUD
  - Site settings (general, email, security, appearance)
  - SMS gateway configuration
  - Artisan console — run whitelisted commands from the browser with a confirmation dialog
- **SQLite out of the box** — swap to MySQL/PostgreSQL by changing `DB_CONNECTION`
- **No build step** — CSS/JS served statically from `public/assets/`; no Node, npm, or Vite required
- **Feature test suite** — auth, password reset, account settings

## Requirements

| Dependency | Version |
|---|---|
| PHP | ≥ 8.3 |
| Composer | 2.x |
| SQLite extension | bundled with most PHP installs |

No Node.js or npm required.

## Quick Start

```bash
# 1. Clone
git clone https://github.com/your-username/laravel-starter.git
cd laravel-starter

# 2. Install dependencies
composer install

# 3. Environment
cp .env.example .env
php artisan key:generate

# 4. Database
touch database/database.sqlite
php artisan migrate
php artisan db:seed

# 5. Serve
php artisan serve
```

Open [http://localhost:8000](http://localhost:8000) and log in with:

| Field    | Value              |
|----------|--------------------|
| Email    | test@example.com   |
| Password | password           |

The test user is seeded with the `super_admin` role.

> **Production note:** Run only `php artisan db:seed --class=RolePermissionSeeder` in production. `DatabaseSeeder` also creates the `test@example.com` dev account.

## Default Roles

| Role          | Access                                                  |
|---------------|---------------------------------------------------------|
| `super_admin` | Bypasses all Gate checks — unrestricted access          |
| `admin`       | View users, roles, and permissions                      |
| `editor`      | View users                                              |
| `viewer`      | View users                                              |

## Admin Panel

All admin routes live under `/admin` and require the `role:super_admin,admin` middleware.

| Section         | URL                   | Who can access                          |
|-----------------|-----------------------|-----------------------------------------|
| Users           | `/admin/users`        | admin, super_admin                      |
| Roles           | `/admin/roles`        | view: admin+; create/edit/delete: super_admin |
| Permissions     | `/admin/permissions`  | admin, super_admin                      |
| Site Settings   | `/admin/settings`     | super_admin                             |
| SMS Gateway     | `/admin/sms-gateway`  | super_admin                             |
| Artisan Console | `/admin/artisan`      | super_admin                             |

### Artisan Console

Lets a `super_admin` run a curated set of artisan commands from the browser. Each command shows a confirmation modal before executing; destructive commands (`migrate`, `migrate:rollback`, `db:seed`) show an additional warning. The allowed command list is a server-side whitelist — no arbitrary input is accepted.

**Available commands:**

| Group    | Commands |
|----------|----------|
| Cache    | `cache:clear`, `config:clear`, `config:cache`, `route:clear`, `route:cache`, `view:clear`, `view:cache`, `optimize`, `optimize:clear` |
| Database | `migrate`, `migrate:rollback`, `migrate:status`, `db:seed` (RolePermissionSeeder only) |
| System   | `storage:link`, `queue:restart` |

## Two-Factor Authentication

Users can enable 2FA from **Profile → Security Settings**:

- **TOTP** — scan a QR code in any authenticator app (Google Authenticator, Authy, 1Password, etc.)
- **SMS** — sends a one-time code via the configured SMS gateway

Site-wide 2FA enforcement can be toggled from **Admin → Site Settings → Security**. When enabled, users who haven't set up 2FA are redirected to the setup page after login.

## Adding a New Module

**1. Add permissions to `RolePermissionSeeder.php`** and re-seed:

```bash
php artisan db:seed --class=RolePermissionSeeder
```

**2. Protect routes in `routes/web.php`:**

```php
Route::middleware('permission:posts.view')->group(function () {
    Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
});
```

**3. Guard Blade buttons with `@can`:**

```blade
@can('posts.create')
    <a href="{{ route('posts.create') }}" class="btn btn-primary">New Post</a>
@endcan
```

**4. Add a menu item in `resources/views/partials/menu.blade.php`:**

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

## Running Tests

```bash
php artisan test
```

| Test file                                  | Coverage                                     |
|--------------------------------------------|----------------------------------------------|
| `tests/Feature/AuthTest.php`               | Login, registration, logout                  |
| `tests/Feature/PasswordResetTest.php`      | Full password-reset flow                     |
| `tests/Feature/AccountSettingsTest.php`    | Profile update, password change              |

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/              # ArtisanController, UserController, RoleController,
│   │   │                       # PermissionController, SiteSettingController, SmsGatewayController
│   │   ├── AuthController.php
│   │   ├── ProfileController.php
│   │   └── TwoFactorController.php
│   └── Middleware/
│       ├── RequireRole.php
│       ├── RequirePermission.php
│       └── TwoFactorMiddleware.php
├── Models/
│   ├── User.php                # roles(), hasRole(), hasPermission()
│   ├── Role.php                # permissions(), hasPermission()
│   ├── Permission.php
│   ├── SiteSetting.php         # key/value store with per-request cache
│   ├── SmsGatewaySetting.php
│   └── TwoFactorCode.php
database/
├── migrations/
└── seeders/
    ├── DatabaseSeeder.php      # dev only — includes test user
    ├── RolePermissionSeeder.php  # safe for production
    └── SiteSettingSeeder.php
resources/views/
├── layouts/                    # app.blade.php (admin shell), auth.blade.php
├── partials/                   # menu, navbar, footer, brand-logo
├── admin/                      # users, roles, permissions, settings, artisan
├── auth/                       # login, register, forgot/reset password, 2FA
├── profile/
└── settings/
```

## Environment Variables

Key variables in `.env`:

| Variable | Default | Purpose |
|---|---|---|
| `APP_NAME` | Laravel | Application name (also configurable via Site Settings) |
| `APP_ENV` | local | Environment (`local` / `production`) |
| `APP_KEY` | — | Generated by `php artisan key:generate` |
| `DB_CONNECTION` | sqlite | Database driver |
| `MAIL_MAILER` | log | Mail driver (`log` writes reset emails to `storage/logs/laravel.log`) |
| `TWILIO_SID` | — | SMS gateway Twilio account SID |
| `TWILIO_TOKEN` | — | SMS gateway Twilio auth token |
| `TWILIO_FROM` | — | SMS gateway sender number |

## UI

Built on the [Sneat Bootstrap 5 Admin Template](https://themeselection.com/item/sneat-bootstrap-html-admin-template/) by ThemeSelection (Pro v1.0.0). Static assets are served from `public/assets/` — no build step needed.

> The Sneat Pro template is commercially licensed. If you clone this repo, ensure you hold a valid license for the template before using it in your own projects.

## License

The application code is open-source under the [MIT License](LICENSE).
