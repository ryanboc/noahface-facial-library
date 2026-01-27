<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;

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

    public function timesheet()
    {
        // 1. Get employees and their logs sorted by time
        $employees = Employee::with(['attendanceLogs' => function($query) {
            $query->orderBy('clock_time', 'asc');
        }])->get();

        $timesheets = [];

        foreach ($employees as $employee) {
            $logs = $employee->attendanceLogs;
            $currentShiftStart = null;
            
            foreach ($logs as $log) {
                $type = strtolower(str_replace(' ', '', $log->event_type));
                
                if (($type === 'clockin' || $type === 'starttask') && !$currentShiftStart) {
                    $currentShiftStart = $log;
                }
                // Find End & Calculate
                elseif (($type === 'clockout' || $type === 'endtask') && $currentShiftStart) {
                    
                    $start = Carbon::parse($currentShiftStart->clock_time);
                    $end   = Carbon::parse($log->clock_time);
                    $duration = $start->diffInMinutes($end) / 60;
                    
                    // Get Rate from the START time
                    $rateInfo = $employee->getRateDetails($start); 
                    $totalPay = $duration * $rateInfo['final_rate'];

                    $timesheets[] = [
                        'date' => $start->format('D, d M Y'), // Format like "Tue, 27 Jan 2026"
                        'employee' => $employee->name,
                        'start' => $start->format('h:i A'),   // Format like "08:33 AM"
                        'end' => $end->format('h:i A'),       // Format like "12:25 PM"
                        
                        'duration' => number_format($duration, 2) . ' hrs',
                        
                        // ADD '$' SIGN AND 2 DECIMAL PLACES HERE
                        'rate' => '$' . number_format($rateInfo['final_rate'], 2) . '/hr',
                        'total_pay' => '$' . number_format($totalPay, 2),
                    ];
                    
                    $currentShiftStart = null; // Reset
                }
            }
        }

        // Return a new view specifically for this table
        return view('attendance.timesheet', compact('timesheets'));
    }
}
