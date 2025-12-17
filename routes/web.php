<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Rules\PermissionAccessController;
use App\Http\Controllers\Rules\PermissionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Test Routes
    Route::get('manage-company', function () {
        return "<h1>Tes halaman dengan permission standard: manage-company</h1>";
    })->middleware('company_permission:manage-company');
    
    Route::get('create-workspace', function () {
        return "<h1>Tes halaman dengan permission unique: create-workspace</h1>";
    })->middleware('company_permission:create-workspace');

    Route::middleware('role:super-admin')->group(function () {
        Route::prefix('access-control')->name('access-control.')->group(function () {
            Route::resource('permissions', PermissionController::class);

            Route::get('company-access', [PermissionAccessController::class, 'index'])->name('company-access.index');
            Route::post('company-access/{company}', [PermissionAccessController::class, 'update'])->name('company-access.update');
        });
    });
});

require __DIR__.'/settings.php';