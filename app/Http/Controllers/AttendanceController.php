<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    public function status(Request $request)
    {
        $request->validate(['company_id' => ['nullable', 'integer', 'exists:companies,id']]);

        $companyId = $request->integer('company_id') ?: null;
        $companies = Company::orderBy('name')->get();
        $selectedCompany = $companyId ? $companies->firstWhere('id', $companyId) : null;

        $employees = Employee::query()
            ->with(['companies', 'attendanceLogs' => fn ($query) => $query
                ->whereDate('clock_time', today())
                ->latest('clock_time')])
            ->when($companyId, fn ($query) => $query->whereHas(
                'companies',
                fn ($query) => $query->whereKey($companyId)
            ))
            ->orderBy('name')
            ->get();

        $groups = collect([
            'clocked_in' => collect(),
            'on_break' => collect(),
            'clocked_out' => collect(),
            'not_clocked_in' => collect(),
        ]);

        foreach ($employees as $employee) {
            $latestLog = $employee->attendanceLogs->first();
            $event = strtolower(preg_replace('/[^a-z0-9]+/i', '', $latestLog?->event_type ?? ''));

            $status = match (true) {
                in_array($event, ['clockin', 'clockon', 'starttask', 'endbreak', 'breakin'], true) => 'clocked_in',
                in_array($event, ['startbreak', 'breakstart', 'breakout'], true) => 'on_break',
                in_array($event, ['clockout', 'endtask'], true) => 'clocked_out',
                default => 'not_clocked_in',
            };

            $groups[$status]->push((object) [
                'employee' => $employee,
                'latestLog' => $latestLog,
            ]);
        }

        return view('attendance.status', compact('groups', 'companies', 'selectedCompany', 'companyId'));
    }

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
        $startDate = $request->input('start_date', now()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $isExport = $request->has('export');
        $request->validate(['company_id' => ['nullable', 'integer', 'exists:companies,id']]);
        $companyId = $request->integer('company_id') ?: null;
        $companies = Company::orderBy('name')->get();
        $selectedCompany = $companyId ? $companies->firstWhere('id', $companyId) : null;

        // 2. QUERY EMPLOYEES (Apply Search Filter)
        $query = \App\Models\Employee::query();

        $query->when($companyId, fn ($query) => $query->whereHas('companies', fn ($query) => $query->whereKey($companyId)));

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        // 3. EAGER LOAD LOGS (Apply Date Filter)
        $employees = $query->with(['attendanceLogs' => function ($q) use ($startDate, $endDate) {
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
                if (($type === 'clockin' || $type === 'starttask') && ! $currentShiftStart) {
                    $currentShiftStart = $log;
                }
                // FIND END
                elseif (($type === 'clockout' || $type === 'endtask') && $currentShiftStart) {

                    // Use your fixed logic (Start -> End)
                    $start = \Carbon\Carbon::parse($currentShiftStart->clock_time);
                    $end = \Carbon\Carbon::parse($log->clock_time);
                    $duration = $start->diffInMinutes($end) / 60; // Fixed order

                    $rateInfo = $employee->getRateDetails($start);
                    $totalPay = $duration * $rateInfo['final_rate'];

                    $startPayload = is_array($currentShiftStart->raw_payload)
                            ? $currentShiftStart->raw_payload
                            : json_decode($currentShiftStart->raw_payload, true);

                    $timesheets[] = [
                        'date_raw' => $start->format('Y-m-d'), // For sorting if needed
                        'date' => $start->format('D, d M Y'),
                        'employee' => $employee->name,
                        'start' => $start->format('h:i A'),
                        'end' => $end->format('h:i A'),
                        'duration' => number_format($duration, 2).' hrs',
                        'rate_label' => $rateInfo['label'], // Added label for CSV context
                        'rate' => '$'.number_format($rateInfo['final_rate'], 2).'/hr',
                        'total_pay' => '$'.number_format($totalPay, 2),
                        'total_pay_raw' => $totalPay, // For summing if needed
                        'device' => $startPayload['device'] ?? 'Unknown Device',
                        'temperature' => $startPayload['temperature'] ?? 'N/A',
                        'method' => $startPayload['method'] ?? 'N/A',
                    ];

                    $currentShiftStart = null;
                }
            }
        }

        // 5. HANDLE CSV EXPORT
        if ($isExport) {
            $headers = ['Date', 'Employee', 'Start Time', 'End Time', 'Duration', 'Rate Label', 'Hourly Rate', 'Total Pay'];

            $callback = function () use ($timesheets, $headers) {
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
                        $row['total_pay'],
                    ]);
                }
                fclose($file);
            };

            return new StreamedResponse($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=timesheets-'.date('Y-m-d').'.csv',
            ]);
        }

        // 6. RETURN VIEW (Pass current filters back to view)
        return view('attendance.timesheet', compact('timesheets', 'search', 'startDate', 'endDate', 'companies', 'selectedCompany', 'companyId'));
    }
}
