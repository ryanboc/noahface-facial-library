<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AwardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');

Route::resource('awards', AwardController::class);

Route::resource('employees', EmployeeController::class);

Route::get('attendance/timesheet', [AttendanceController::class, 'timesheet'])->name('attendance.timesheet');