<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\RosterShift;
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

        return view('roster.index', compact('weekStart', 'weekEnd', 'employees', 'shifts', 'leave', 'days', 'totalHours'));
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

        RosterShift::create($data);
        return redirect()->route('roster.index', ['week' => Carbon::parse($data['shift_date'])->startOfWeek()->toDateString()])->with('success', 'Shift added to the roster.');
    }

    public function destroy(RosterShift $rosterShift)
    {
        $week = $rosterShift->shift_date->copy()->startOfWeek()->toDateString();
        $rosterShift->delete();
        return redirect()->route('roster.index', ['week' => $week])->with('success', 'Shift removed.');
    }

    public function print(Request $request)
    {
        [$weekStart, $weekEnd] = $this->week($request);
        $shifts = RosterShift::with('employee')->whereBetween('shift_date', [$weekStart, $weekEnd])->orderBy('shift_date')->orderBy('start_time')->get();
        $days = collect(range(0, 6))->map(fn ($day) => $weekStart->copy()->addDays($day));
        return view('roster.print', compact('weekStart', 'weekEnd', 'shifts', 'days'));
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
}
