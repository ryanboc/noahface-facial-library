<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AwardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController; 
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\RosterController;
use App\Http\Controllers\ProfileController;

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
    if (auth()->check() && ! auth()->user()->canApproveLeave()) {
        return redirect()->route('profile.show');
    }

    return redirect()->route('attendance.timesheet');
});

// Protected Routes (Must be logged in to access these)
Route::middleware('auth')->group(function () {

    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('profile/leave', [ProfileController::class, 'requestLeave'])->name('profile.leave.store');

    // 2FA Setup Routes
    Route::get('2fa/setup', [TwoFactorController::class, 'showSetup'])->name('2fa.setup');
    Route::post('2fa/setup', [TwoFactorController::class, 'enable'])->name('2fa.enable');

    Route::middleware('management')->group(function () {
        Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::resource('awards', AwardController::class);
        Route::resource('employees', EmployeeController::class);
        Route::get('attendance/timesheet', [AttendanceController::class, 'timesheet'])->name('attendance.timesheet');
        Route::get('leave', [LeaveRequestController::class, 'index'])->name('leave.index');
        Route::post('leave', [LeaveRequestController::class, 'store'])->name('leave.store');
        Route::patch('leave/{leaveRequest}/review', [LeaveRequestController::class, 'review'])->name('leave.review');
        Route::get('roster', [RosterController::class, 'index'])->name('roster.index');
        Route::post('roster', [RosterController::class, 'store'])->name('roster.store');
        Route::delete('roster/{rosterShift}', [RosterController::class, 'destroy'])->name('roster.destroy');
        Route::get('roster/print', [RosterController::class, 'print'])->name('roster.print');
        Route::get('roster/pdf', [RosterController::class, 'downloadPdf'])->name('roster.pdf');
    });
});
