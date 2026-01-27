<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        // Fetch logs and grab the 'Employee' and their 'Award' in one go
        $logs = AttendanceLog::with('employee.award')
            ->latest('clock_time')
            ->paginate(20);

        return view('attendance.index', compact('logs'));
    }
}
