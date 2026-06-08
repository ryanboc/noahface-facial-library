<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AwardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController; 
use App\Http\Controllers\TwoFactorController;

// Public Routes (Anyone can see these)
Route::middleware('guest')->group(function () {
    // Login
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');

    // Registration (NEW)
    Route::get('register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [AuthController::class, 'register'])->name('register.submit');

    // 2FA Challenge / Holding Pattern (NEW)
    Route::get('2fa/challenge', [TwoFactorController::class, 'showChallenge'])->name('2fa.challenge');
    Route::post('2fa/challenge', [TwoFactorController::class, 'verify'])->name('2fa.verify');
});

Route::post('logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', function () {
    return redirect()->route('attendance.timesheet');
});

// Protected Routes (Must be logged in to access these)
Route::middleware('auth')->group(function () {

    // 2FA Setup Routes
    Route::get('2fa/setup', [TwoFactorController::class, 'showSetup'])->name('2fa.setup');
    Route::post('2fa/setup', [TwoFactorController::class, 'enable'])->name('2fa.enable');

    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::resource('awards', AwardController::class);
    Route::resource('employees', EmployeeController::class);
    Route::get('attendance/timesheet', [AttendanceController::class, 'timesheet'])->name('attendance.timesheet');
});