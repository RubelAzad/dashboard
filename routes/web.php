<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', DashboardController::class)
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    // Resource controllers apply "permission:<module>.<action>" middleware themselves.
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class)->except('show');
    Route::resource('permissions', PermissionController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::get('/settings', [SettingController::class, 'index'])
        ->middleware('permission:settings.view')
        ->name('settings.index');
    Route::put('/settings', [SettingController::class, 'update'])
        ->middleware('permission:settings.update')
        ->name('settings.update');
});
