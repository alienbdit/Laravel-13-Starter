<?php

namespace App\Providers;

use App\Encryption\Encrypter;
use App\Models\Permission;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // PHP 8.5 workaround: openssl_encrypt() with a by-ref $tag argument is broken
        // for non-AEAD ciphers in the built-in web server. Replace with a fixed subclass.
        $this->app->singleton('encrypter', function ($app) {
            $config = $app->make('config')->get('app');
            $key    = $config['key'];

            if (str_starts_with($key, 'base64:')) {
                $key = base64_decode(substr($key, 7));
            }

            return new Encrypter($key, $config['cipher'] ?? 'AES-256-CBC');
        });
    }

    public function boot(): void
    {
        // super_admin bypasses all Gate checks
        Gate::before(function (User $user, string $ability) {
            if ($user->hasRole('super_admin')) {
                return true;
            }
        });

        // Define each permission as a Gate ability, loaded from DB
        // Also apply site settings (app name, timezone, mail sender, etc.)
        try {
            Permission::all()->each(function (Permission $permission) {
                Gate::define($permission->name, fn (User $user) => $user->hasPermission($permission->name));
            });

            SiteSetting::applyToConfig();

            // Apply session lifetime from settings
            $lifetime = (int) SiteSetting::get('session_lifetime', 120);
            config(['session.lifetime' => $lifetime]);
        } catch (\Throwable) {
            // DB not ready yet (fresh install before migrations)
        }
    }
}
