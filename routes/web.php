<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AppearanceController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
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

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::patch('/appearance', [AppearanceController::class, 'updateMine'])->name('appearance.update');
    Route::delete('/appearance', [AppearanceController::class, 'resetMine'])->name('appearance.reset');
    Route::put('/settings/appearance', [AppearanceController::class, 'updateDefault'])
        ->middleware('permission:appearance.update')
        ->name('settings.appearance');

    Route::get('/dashboard', DashboardController::class)
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    // Resource controllers apply "permission:<module>.<action>" middleware themselves.
    // Create and edit forms are modals on the index/show pages, so those routes do not exist.
    Route::resource('users', UserController::class)->except(['create', 'edit']);
    Route::resource('roles', RoleController::class)->except(['show', 'create', 'edit']);
    Route::resource('permissions', PermissionController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::get('/logs/activity', [ActivityLogController::class, 'activity'])->name('logs.activity');
    Route::get('/logs/audit', [ActivityLogController::class, 'audit'])->name('logs.audit');

    Route::get('/settings', [SettingController::class, 'index'])
        ->middleware('permission:settings.view')
        ->name('settings.index');
    Route::put('/settings', [SettingController::class, 'update'])
        ->middleware('permission:settings.update')
        ->name('settings.update');
});
