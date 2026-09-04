<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
                'warning' => $this->statusWarning($status, $employee->attendanceLogs),
            ]);
        }

        return view('attendance.status', compact('groups', 'companies', 'selectedCompany', 'companyId', 'employees'));
    }

    public function storeAdjustment(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'event_type' => ['required', Rule::in(['Clock In', 'Start Break', 'End Break', 'Clock Out'])],
            'clock_time' => ['required', 'date', 'before_or_equal:now'],
            'adjustment_reason' => ['required', 'string', 'min:5', 'max:1000'],
            'return_to' => ['nullable', Rule::in(['status', 'timesheet'])],
        ]);

        AttendanceLog::create([
            'employee_id' => $data['employee_id'],
            'event_type' => $data['event_type'],
            'clock_time' => $data['clock_time'],
            'is_manual' => true,
            'adjustment_reason' => $data['adjustment_reason'],
            'adjusted_by' => $request->user()->id,
            'raw_payload' => ['source' => 'manager_adjustment'],
        ]);

        return redirect()->route(($data['return_to'] ?? 'status') === 'timesheet' ? 'attendance.timesheet' : 'attendance.status')
            ->with('success', 'Attendance correction added. The original records were not changed.');
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

        // Build completed shifts and surface incomplete event sequences for review.
        $timesheets = [];
        $exceptions = [];

        foreach ($employees as $employee) {
            $logs = $employee->attendanceLogs;
            $currentShiftStart = null;
            $breakStart = null;
            $breakMinutes = 0;

            foreach ($logs as $log) {
                $type = strtolower(preg_replace('/[^a-z0-9]+/i', '', $log->event_type));

                // FIND START
                if (($type === 'clockin' || $type === 'starttask') && ! $currentShiftStart) {
                    $currentShiftStart = $log;
                    $breakStart = null;
                    $breakMinutes = 0;
                }
                elseif (in_array($type, ['startbreak', 'breakstart', 'breakout'], true) && $currentShiftStart && ! $breakStart) {
                    $breakStart = $log;
                }
                elseif (in_array($type, ['endbreak', 'breakin'], true) && $currentShiftStart && $breakStart) {
                    $breakMinutes += $breakStart->clock_time->diffInMinutes($log->clock_time);
                    $breakStart = null;
                }
                // FIND END
                elseif (($type === 'clockout' || $type === 'endtask') && $currentShiftStart) {

                    if ($breakStart) {
                        $exceptions[] = $this->exceptionRow($employee, $currentShiftStart, 'Break was started but never ended.');
                        $currentShiftStart = null;
                        $breakStart = null;
                        $breakMinutes = 0;
                        continue;
                    }

                    // Use your fixed logic (Start -> End)
                    $start = \Carbon\Carbon::parse($currentShiftStart->clock_time);
                    $end = \Carbon\Carbon::parse($log->clock_time);
                    $workedMinutes = max(0, $start->diffInMinutes($end) - $breakMinutes);
                    $duration = $workedMinutes / 60;

                    $rateInfo = $employee->getRateDetails($start, $end);
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
                        'break_duration' => $breakMinutes ? $this->formatMinutes($breakMinutes) : 'None',
                        'rate_label' => $rateInfo['label'], // Added label for CSV context
                        'rate' => '$'.number_format($rateInfo['final_rate'], 2).'/hr',
                        'total_pay' => '$'.number_format($totalPay, 2),
                        'total_pay_raw' => $totalPay, // For summing if needed
                        'device' => $startPayload['device'] ?? 'Unknown Device',
                        'temperature' => $startPayload['temperature'] ?? 'N/A',
                        'method' => $startPayload['method'] ?? 'N/A',
                    ];

                    $currentShiftStart = null;
                    $breakMinutes = 0;
                }
            }

            if ($currentShiftStart) {
                $exceptions[] = $this->exceptionRow(
                    $employee,
                    $currentShiftStart,
                    $breakStart ? 'Break was started but never ended.' : 'Shift was started but never clocked out.'
                );
            }
        }

        // 5. HANDLE CSV EXPORT
        if ($isExport) {
            $headers = ['Date', 'Employee', 'Start Time', 'End Time', 'Break', 'Paid Duration', 'Rate Label', 'Hourly Rate', 'Total Pay'];

            $callback = function () use ($timesheets, $headers) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $headers);

                foreach ($timesheets as $row) {
                    fputcsv($file, [
                        $row['date'],
                        $row['employee'],
                        $row['start'],
                        $row['end'],
                        $row['break_duration'],
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
        return view('attendance.timesheet', compact('timesheets', 'exceptions', 'search', 'startDate', 'endDate', 'companies', 'selectedCompany', 'companyId', 'employees'));
    }

    private function statusWarning(string $status, $logs): ?string
    {
        $log = $logs->first();

        if (! $log) {
            return null;
        }

        $openShiftStart = $logs->first(function ($item) {
            $event = strtolower(preg_replace('/[^a-z0-9]+/i', '', $item->event_type));

            return in_array($event, ['clockin', 'clockon', 'starttask'], true);
        });

        $minutes = ($status === 'clocked_in' ? $openShiftStart?->clock_time : $log->clock_time)?->diffInMinutes(now()) ?? 0;

        return match (true) {
            $status === 'on_break' && $minutes > 60 => 'Break has been open for more than 60 minutes.',
            $status === 'clocked_in' && $minutes > 720 => 'Shift has been open for more than 12 hours.',
            default => null,
        };
    }

    private function exceptionRow(Employee $employee, AttendanceLog $start, string $issue): array
    {
        return [
            'employee_id' => $employee->id,
            'employee' => $employee->name,
            'date' => $start->clock_time->format('D, d M Y'),
            'start' => $start->clock_time->format('h:i A'),
            'issue' => $issue,
        ];
    }

    private function formatMinutes(int $minutes): string
    {
        return sprintf('%dh %02dm', intdiv($minutes, 60), $minutes % 60);
    }
}
