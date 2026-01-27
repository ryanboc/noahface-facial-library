<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function timesheet(Request $request)
    {
        // 1. CAPTURE FILTERS
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $isExport = $request->has('export');

        // 2. QUERY EMPLOYEES (Apply Search Filter)
        $query = \App\Models\Employee::query();
        
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        // 3. EAGER LOAD LOGS (Apply Date Filter)
        $employees = $query->with(['attendanceLogs' => function($q) use ($startDate, $endDate) {
            $q->orderBy('clock_time', 'asc');
            
            // Only filter by date if provided
            if ($startDate) {
                $q->whereDate('clock_time', '>=', $startDate);
            }
            if ($endDate) {
                $q->whereDate('clock_time', '<=', $endDate);
            }
        }])->get();

        // 4. CALCULATE TIMESHEETS (Your existing logic)
        $timesheets = [];

        foreach ($employees as $employee) {
            $logs = $employee->attendanceLogs;
            $currentShiftStart = null;
            
            foreach ($logs as $log) {
                $type = strtolower(str_replace(' ', '', $log->event_type));
                
                // FIND START
                if (($type === 'clockin' || $type === 'starttask') && !$currentShiftStart) {
                    $currentShiftStart = $log;
                }
                // FIND END
                elseif (($type === 'clockout' || $type === 'endtask') && $currentShiftStart) {
                    
                    // Use your fixed logic (Start -> End)
                    $start = \Carbon\Carbon::parse($currentShiftStart->clock_time);
                    $end   = \Carbon\Carbon::parse($log->clock_time);
                    $duration = $start->diffInMinutes($end) / 60; // Fixed order
                    
                    $rateInfo = $employee->getRateDetails($start); 
                    $totalPay = $duration * $rateInfo['final_rate'];

                    $timesheets[] = [
                        'date_raw'  => $start->format('Y-m-d'), // For sorting if needed
                        'date'      => $start->format('D, d M Y'),
                        'employee'  => $employee->name,
                        'start'     => $start->format('h:i A'),
                        'end'       => $end->format('h:i A'),
                        'duration'  => number_format($duration, 2) . ' hrs',
                        'rate_label'=> $rateInfo['label'], // Added label for CSV context
                        'rate'      => '$' . number_format($rateInfo['final_rate'], 2) . '/hr',
                        'total_pay' => '$' . number_format($totalPay, 2),
                        'total_pay_raw' => $totalPay // For summing if needed
                    ];
                    
                    $currentShiftStart = null;
                }
            }
        }

        // 5. HANDLE CSV EXPORT
        if ($isExport) {
            $headers = ['Date', 'Employee', 'Start Time', 'End Time', 'Duration', 'Rate Label', 'Hourly Rate', 'Total Pay'];
            
            $callback = function() use ($timesheets, $headers) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $headers);

                foreach ($timesheets as $row) {
                    fputcsv($file, [
                        $row['date'],
                        $row['employee'],
                        $row['start'],
                        $row['end'],
                        $row['duration'],
                        $row['rate_label'],
                        $row['rate'],
                        $row['total_pay']
                    ]);
                }
                fclose($file);
            };

            return new StreamedResponse($callback, 200, [
                "Content-Type" => "text/csv",
                "Content-Disposition" => "attachment; filename=timesheets-" . date('Y-m-d') . ".csv",
            ]);
        }

        // 6. RETURN VIEW (Pass current filters back to view)
        return view('attendance.timesheet', compact('timesheets', 'search', 'startDate', 'endDate'));
    }
}
