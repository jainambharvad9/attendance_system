<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : view('landing');
})->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');

    Route::middleware('role:admin')->group(function () {
        Route::resource('locations', LocationController::class)->except(['show']);
        Route::get('/admin/staff', [AdminStaffController::class, 'index'])->name('admin.staff.index');
        Route::get('/admin/staff/{staff}/password', [AdminStaffController::class, 'editPassword'])->name('admin.staff.password.edit');
        Route::put('/admin/staff/{staff}/password', [AdminStaffController::class, 'updatePassword'])->name('admin.staff.password.update');
        Route::get('/admin/staff/create', [AdminStaffController::class, 'create'])->name('admin.staff.create');
        Route::post('/admin/staff', [AdminStaffController::class, 'store'])->name('admin.staff.store');
    });
});
