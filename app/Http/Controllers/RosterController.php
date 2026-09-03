<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\MessageTemplate;
use App\Models\RosterShift;
use App\Models\TextMessage;
use App\Services\TwilioService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class RosterController extends Controller
{
    public function index(Request $request)
    {
        [$weekStart, $weekEnd] = $this->week($request);
        $companies = Company::orderBy('name')->get();
        $selectedCompany = $companies->firstWhere('id', $request->integer('company_id'))
            ?? $companies->firstWhere('name', 'Inglewood Farms')
            ?? $companies->first();
        $employees = $selectedCompany ? $selectedCompany->employees()->orderBy('name')->get() : collect();
        $shifts = RosterShift::with('employee')->where(function ($query) use ($selectedCompany) {
            $query->where('company_id', $selectedCompany?->id)
                ->orWhere(fn ($query) => $query->whereNull('company_id')->whereHas('employee.companies', fn ($query) => $query->whereKey($selectedCompany?->id)));
        })->whereBetween('shift_date', [$weekStart, $weekEnd])->orderBy('start_time')->get();
        $leave = LeaveRequest::with('employee')->whereIn('employee_id', $employees->modelKeys())->where('status', 'approved')->whereDate('start_date', '<=', $weekEnd)->whereDate('end_date', '>=', $weekStart)->get();
        $days = collect(range(0, 6))->map(fn ($day) => $weekStart->copy()->addDays($day));
        $totalHours = $shifts->sum(fn ($shift) => $this->duration($shift->start_time, $shift->end_time));
        $employeeHours = $shifts->groupBy('employee_id')->map(fn ($employeeShifts) => $employeeShifts->sum(fn ($shift) => $this->duration($shift->start_time, $shift->end_time))
        );
        $messageTemplates = MessageTemplate::orderBy('name')->get();
        $rosteredEmployees = $shifts->pluck('employee')->unique('id')->values();

        return view('roster.index', compact('weekStart', 'weekEnd', 'companies', 'selectedCompany', 'employees', 'shifts', 'leave', 'days', 'totalHours', 'employeeHours', 'messageTemplates', 'rosteredEmployees'));
    }

    public function store(Request $request)
    {
        if (! $request->filled('company_id') && $request->filled('employee_id')) {
            $request->merge(['company_id' => Employee::find($request->integer('employee_id'))?->companies()->value('companies.id')]);
        }

        $data = $request->validate([
            'company_id' => ['required', 'exists:companies,id'], 'employee_id' => ['required', 'exists:employees,id'], 'shift_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'], 'end_time' => ['required', 'date_format:H:i', 'different:start_time'],
            'location' => ['nullable', 'string', 'max:100'], 'role' => ['nullable', 'string', 'max:100'], 'notes' => ['nullable', 'string', 'max:500'],
        ]);
        $employee = Employee::findOrFail($data['employee_id']);
        if (! $employee->companies()->whereKey($data['company_id'])->exists()) {
            throw ValidationException::withMessages(['employee_id' => 'This employee is not assigned to the selected company.']);
        }
        $onLeave = LeaveRequest::where('employee_id', $data['employee_id'])->where('status', 'approved')
            ->whereDate('start_date', '<=', $data['shift_date'])->whereDate('end_date', '>=', $data['shift_date'])->exists();
        if ($onLeave) {
            throw ValidationException::withMessages(['employee_id' => 'This employee has approved leave on the selected date.']);
        }

        $proposedDate = Carbon::parse($data['shift_date']);
        [$proposedStart, $proposedEnd] = $this->shiftInterval($proposedDate, $data['start_time'], $data['end_time']);
        $overlappingShift = RosterShift::where('employee_id', $data['employee_id'])
            ->whereBetween('shift_date', [$proposedDate->copy()->subDay(), $proposedDate->copy()->addDay()])
            ->get()
            ->first(function ($shift) use ($proposedStart, $proposedEnd) {
                [$existingStart, $existingEnd] = $this->shiftInterval($shift->shift_date, $shift->start_time, $shift->end_time);

                return $proposedStart->lt($existingEnd) && $proposedEnd->gt($existingStart);
            });
        if ($overlappingShift) {
            throw ValidationException::withMessages([
                'start_time' => "{$employee->name} already has an overlapping shift from ".Carbon::parse($overlappingShift->start_time)->format('g:i A').' to '.Carbon::parse($overlappingShift->end_time)->format('g:i A').' on '.$overlappingShift->shift_date->format('d M Y').'.',
            ]);
        }

        $weekStart = Carbon::parse($data['shift_date'])->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();
        $existingHours = RosterShift::where('employee_id', $data['employee_id'])
            ->whereBetween('shift_date', [$weekStart, $weekEnd])
            ->get(['start_time', 'end_time'])
            ->sum(fn ($shift) => $this->duration($shift->start_time, $shift->end_time));
        $weeklyHours = $existingHours + $this->duration($data['start_time'], $data['end_time']);

        if ($weeklyHours > 40) {
            throw ValidationException::withMessages([
                'end_time' => "This shift would roster {$employee->name} for ".number_format($weeklyHours, 1).' hours this week, exceeding the 40-hour limit.',
            ]);
        }

        RosterShift::create($data);
        $message = 'Shift added to the roster.';
        if ($weeklyHours > 38) {
            $message .= " Warning: {$employee->name} is rostered for ".number_format($weeklyHours, 1).' hours this week, above the standard 38 hours.';
        }

        return redirect()->route('roster.index', ['week' => $weekStart->toDateString(), 'company_id' => $data['company_id']])->with('success', $message);
    }

    public function destroy(RosterShift $rosterShift)
    {
        $week = $rosterShift->shift_date->copy()->startOfWeek()->toDateString();
        $companyId = $rosterShift->company_id;
        $rosterShift->delete();

        return redirect()->route('roster.index', ['week' => $week, 'company_id' => $companyId])->with('success', 'Shift removed.');
    }

    public function print(Request $request)
    {
        return view('roster.print', $this->printData($request));
    }

    public function downloadPdf(Request $request)
    {
        $data = $this->printData($request);
        $data['isPdf'] = true;
        $filename = 'roster-week-'.$data['weekStart']->format('Y-m-d').'.pdf';

        return Pdf::loadView('roster.print', $data)
            ->setPaper('a4', 'landscape')
            ->download($filename);
    }

    public function sendWeeklyRoster(Request $request, TwilioService $twilio)
    {
        $data = $request->validate([
            'week' => ['required', 'date'],
            'company_id' => ['required', 'exists:companies,id'],
            'message_template_id' => ['nullable', 'exists:message_templates,id'],
        ]);
        $weekStart = Carbon::parse($data['week'])->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();
        $company = Company::findOrFail($data['company_id']);
        $template = isset($data['message_template_id'])
            ? MessageTemplate::findOrFail($data['message_template_id'])
            : null;
        $shifts = RosterShift::with('employee')
            ->where('company_id', $company->id)
            ->whereBetween('shift_date', [$weekStart, $weekEnd])
            ->orderBy('shift_date')->orderBy('start_time')->get();

        $sent = 0;
        $failed = 0;
        $missingPhone = [];
        foreach ($shifts->groupBy('employee_id') as $employeeShifts) {
            $employee = $employeeShifts->first()->employee;
            if (! $employee->phone || ! preg_match('/^\+[1-9]\d{7,14}$/', $employee->phone)) {
                $missingPhone[] = $employee->name;

                continue;
            }

            $schedule = $employeeShifts->map(function ($shift) {
                $details = collect([$shift->role, $shift->location])->filter()->join(', ');

                return $shift->shift_date->format('D d M').' '.Carbon::parse($shift->start_time)->format('g:i A').'-'.Carbon::parse($shift->end_time)->format('g:i A').($details ? " ({$details})" : '');
            })->join("\n");
            $defaultBody = "Hi {name}, your roster for the week of {week} is:\n{roster}";
            $templateBody = $template?->body ?? $defaultBody;
            if (! str_contains($templateBody, '{roster}')) {
                $templateBody .= "\n\nYour roster:\n{roster}";
            }
            $body = strtr($templateBody, [
                '{name}' => $employee->name,
                '{week}' => $weekStart->format('d M Y'),
                '{company}' => $company->name,
                '{roster}' => $schedule,
            ]);
            $message = TextMessage::create([
                'message_template_id' => $template?->id,
                'sent_by' => $request->user()->id,
                'recipient' => $employee->phone,
                'body' => $body,
            ]);

            try {
                $result = $twilio->send($employee->phone, $body);
                $message->update(['twilio_sid' => $result['sid'], 'status' => $result['status'], 'sent_at' => now()]);
                $sent++;
            } catch (Throwable $exception) {
                report($exception);
                $message->update(['status' => 'failed', 'error_message' => $exception->getMessage()]);
                $failed++;
            }
        }

        $summary = "Weekly roster processed: {$sent} sent";
        if ($failed) {
            $summary .= ", {$failed} failed";
        }
        if ($missingPhone) {
            $summary .= '. Missing mobile number: '.implode(', ', $missingPhone);
        }

        return redirect()->route('roster.index', ['week' => $weekStart->toDateString(), 'company_id' => $company->id])
            ->with($sent ? 'success' : 'warning', $summary.'.');
    }

    private function printData(Request $request): array
    {
        [$weekStart, $weekEnd] = $this->week($request);
        $companies = Company::orderBy('name')->get();
        $selectedCompany = $companies->firstWhere('id', $request->integer('company_id'))
            ?? $companies->firstWhere('name', 'Inglewood Farms')
            ?? $companies->first();
        $employeeIds = $selectedCompany?->employees()->pluck('employees.id') ?? collect();
        $shifts = RosterShift::with('employee')->where(function ($query) use ($selectedCompany) {
            $query->where('company_id', $selectedCompany?->id)
                ->orWhere(fn ($query) => $query->whereNull('company_id')->whereHas('employee.companies', fn ($query) => $query->whereKey($selectedCompany?->id)));
        })->whereBetween('shift_date', [$weekStart, $weekEnd])->orderBy('shift_date')->orderBy('start_time')->get();
        $leave = LeaveRequest::with('employee')->whereIn('employee_id', $employeeIds)->where('status', 'approved')
            ->whereDate('start_date', '<=', $weekEnd)->whereDate('end_date', '>=', $weekStart)->get();
        $days = collect(range(0, 6))->map(fn ($day) => $weekStart->copy()->addDays($day));

        return compact('weekStart', 'weekEnd', 'selectedCompany', 'shifts', 'leave', 'days');
    }

    private function week(Request $request): array
    {
        try {
            $start = Carbon::parse($request->input('week', now()))->startOfWeek();
        } catch (\Throwable) {
            $start = now()->startOfWeek();
        }

        return [$start, $start->copy()->endOfWeek()];
    }

    private function duration(string $start, string $end): float
    {
        $from = Carbon::parse($start);
        $to = Carbon::parse($end);
        if ($to->lte($from)) {
            $to->addDay();
        }

        return $from->diffInMinutes($to) / 60;
    }

    private function shiftInterval(Carbon $date, string $start, string $end): array
    {
        $from = $date->copy()->setTimeFromTimeString($start);
        $to = $date->copy()->setTimeFromTimeString($end);
        if ($to->lte($from)) {
            $to->addDay();
        }

        return [$from, $to];
    }
}
