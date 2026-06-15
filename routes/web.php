<?php

use App\Http\Controllers\Admin\ArtisanController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\SmsGatewayController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TwoFactorController;
use Illuminate\Support\Facades\Route;

// 2FA verification — no auth/guest guard; controller validates session
Route::get('/two-factor', [TwoFactorController::class, 'showVerify'])->name('two-factor.verify');
Route::post('/two-factor', [TwoFactorController::class, 'verify'])->name('two-factor.post');
Route::post('/two-factor/resend', [TwoFactorController::class, 'resend'])->name('two-factor.resend');

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('dashboard');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo.update');
    Route::delete('/profile/photo', [ProfileController::class, 'removePhoto'])->name('profile.photo.remove');

    // Serve private avatar images (any authenticated user may view)
    Route::get('/avatar/{userId}', [ProfileController::class, 'servePhoto'])->name('profile.avatar');

    // Security / 2FA settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/security', [TwoFactorController::class, 'showSecurity'])->name('security');
        Route::post('/two-factor/enable', [TwoFactorController::class, 'enable'])->name('two-factor.enable');
        Route::get('/two-factor/setup-totp', [TwoFactorController::class, 'showSetupTotp'])->name('two-factor.setup-totp');
        Route::post('/two-factor/confirm-totp', [TwoFactorController::class, 'confirmTotp'])->name('two-factor.confirm-totp');
        Route::delete('/two-factor/disable', [TwoFactorController::class, 'disable'])->name('two-factor.disable');
    });

    Route::prefix('admin')->name('admin.')->middleware('role:super_admin,admin')->group(function () {

        // Users
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create')->middleware('permission:users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store')->middleware('permission:users.create');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit')->middleware('permission:users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update')->middleware('permission:users.edit');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('permission:users.delete');

        // Roles
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create')->middleware('role:super_admin');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store')->middleware('role:super_admin');
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit')->middleware('role:super_admin');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update')->middleware('role:super_admin');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy')->middleware('role:super_admin');

        // Site Settings (super_admin only)
        Route::get('/settings', [SiteSettingController::class, 'index'])->name('settings.index')->middleware('role:super_admin');
        Route::put('/settings/general', [SiteSettingController::class, 'updateGeneral'])->name('settings.update-general')->middleware('role:super_admin');
        Route::put('/settings/email', [SiteSettingController::class, 'updateEmail'])->name('settings.update-email')->middleware('role:super_admin');
        Route::put('/settings/security', [SiteSettingController::class, 'updateSecurity'])->name('settings.update-security')->middleware('role:super_admin');
        Route::post('/settings/appearance', [SiteSettingController::class, 'updateAppearance'])->name('settings.update-appearance')->middleware('role:super_admin');

        // SMS Gateway (super_admin only)
        Route::get('/sms-gateway', [SmsGatewayController::class, 'index'])->name('sms-gateway.index')->middleware('role:super_admin');
        Route::put('/sms-gateway', [SmsGatewayController::class, 'update'])->name('sms-gateway.update')->middleware('role:super_admin');
        Route::post('/sms-gateway/test', [SmsGatewayController::class, 'test'])->name('sms-gateway.test')->middleware('role:super_admin');

        // Artisan Console (super_admin only)
        Route::get('/artisan', [ArtisanController::class, 'index'])->name('artisan.index')->middleware('role:super_admin');
        Route::post('/artisan/run', [ArtisanController::class, 'run'])->name('artisan.run')->middleware('role:super_admin');

        // Permissions
        Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
        Route::get('/permissions/create', [PermissionController::class, 'create'])->name('permissions.create')->middleware('permission:permissions.create');
        Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store')->middleware('permission:permissions.create');
        Route::get('/permissions/{permission}/edit', [PermissionController::class, 'edit'])->name('permissions.edit')->middleware('permission:permissions.edit');
        Route::put('/permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update')->middleware('permission:permissions.edit');
        Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy')->middleware('permission:permissions.delete');
    });
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.attempt');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});
