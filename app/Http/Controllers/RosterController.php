<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\RosterShift;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RosterController extends Controller
{
    public function index(Request $request)
    {
        [$weekStart, $weekEnd] = $this->week($request);
        $employees = Employee::orderBy('name')->get();
        $shifts = RosterShift::with('employee')->whereBetween('shift_date', [$weekStart, $weekEnd])->orderBy('start_time')->get();
        $leave = LeaveRequest::with('employee')->where('status', 'approved')->whereDate('start_date', '<=', $weekEnd)->whereDate('end_date', '>=', $weekStart)->get();
        $days = collect(range(0, 6))->map(fn ($day) => $weekStart->copy()->addDays($day));
        $totalHours = $shifts->sum(fn ($shift) => $this->duration($shift->start_time, $shift->end_time));
        $employeeHours = $shifts->groupBy('employee_id')->map(fn ($employeeShifts) =>
            $employeeShifts->sum(fn ($shift) => $this->duration($shift->start_time, $shift->end_time))
        );

        return view('roster.index', compact('weekStart', 'weekEnd', 'employees', 'shifts', 'leave', 'days', 'totalHours', 'employeeHours'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'], 'shift_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'], 'end_time' => ['required', 'date_format:H:i', 'different:start_time'],
            'location' => ['nullable', 'string', 'max:100'], 'role' => ['nullable', 'string', 'max:100'], 'notes' => ['nullable', 'string', 'max:500'],
        ]);
        $onLeave = LeaveRequest::where('employee_id', $data['employee_id'])->where('status', 'approved')
            ->whereDate('start_date', '<=', $data['shift_date'])->whereDate('end_date', '>=', $data['shift_date'])->exists();
        if ($onLeave) throw ValidationException::withMessages(['employee_id' => 'This employee has approved leave on the selected date.']);

        $employee = Employee::findOrFail($data['employee_id']);
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

        return redirect()->route('roster.index', ['week' => $weekStart->toDateString()])->with('success', $message);
    }

    public function destroy(RosterShift $rosterShift)
    {
        $week = $rosterShift->shift_date->copy()->startOfWeek()->toDateString();
        $rosterShift->delete();
        return redirect()->route('roster.index', ['week' => $week])->with('success', 'Shift removed.');
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

    private function printData(Request $request): array
    {
        [$weekStart, $weekEnd] = $this->week($request);
        $shifts = RosterShift::with('employee')->whereBetween('shift_date', [$weekStart, $weekEnd])->orderBy('shift_date')->orderBy('start_time')->get();
        $leave = LeaveRequest::with('employee')->where('status', 'approved')
            ->whereDate('start_date', '<=', $weekEnd)->whereDate('end_date', '>=', $weekStart)->get();
        $days = collect(range(0, 6))->map(fn ($day) => $weekStart->copy()->addDays($day));
        return compact('weekStart', 'weekEnd', 'shifts', 'leave', 'days');
    }

    private function week(Request $request): array
    {
        try { $start = Carbon::parse($request->input('week', now()))->startOfWeek(); }
        catch (\Throwable) { $start = now()->startOfWeek(); }
        return [$start, $start->copy()->endOfWeek()];
    }

    private function duration(string $start, string $end): float
    {
        $from = Carbon::parse($start); $to = Carbon::parse($end); if ($to->lte($from)) $to->addDay();
        return $from->diffInMinutes($to) / 60;
    }

    private function shiftInterval(Carbon $date, string $start, string $end): array
    {
        $from = $date->copy()->setTimeFromTimeString($start);
        $to = $date->copy()->setTimeFromTimeString($end);
        if ($to->lte($from)) $to->addDay();
        return [$from, $to];
    }
}
