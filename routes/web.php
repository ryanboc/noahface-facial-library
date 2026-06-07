<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AwardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController; // <-- Import the new controller

// Public Routes (Anyone can see the login page)
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');
});

Route::post('logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', function () {
    return redirect()->route('attendance.timesheet');
});

// Protected Routes (Must be logged in to access these)
Route::middleware('auth')->group(function () {
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::resource('awards', AwardController::class);
    Route::resource('employees', EmployeeController::class);
    Route::get('attendance/timesheet', [AttendanceController::class, 'timesheet'])->name('attendance.timesheet');
});