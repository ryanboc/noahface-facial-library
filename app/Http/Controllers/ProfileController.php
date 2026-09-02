<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $employee = $request->user()->employee;

        if (! $employee) {
            return view('profile.show', ['employee' => null]);
        }

        $today = today();
        $upcomingShifts = $employee->rosterShifts()->whereDate('shift_date', '>=', $today)->orderBy('shift_date')->orderBy('start_time')->limit(10)->get();
        $currentRequests = $employee->leaveRequests()->with('reviewer')->whereDate('end_date', '>=', $today)->latest()->get();
        $pastLeaves = $employee->leaveRequests()->with('reviewer')->whereDate('end_date', '<', $today)->latest('end_date')->paginate(10);
        $usedAnnualLeave = $employee->leaveRequests()->where('leave_type', 'Annual leave')->where('status', 'approved')
            ->whereYear('start_date', $today->year)->get()->sum(fn ($leave) => $leave->start_date->diffInDays($leave->end_date) + 1);
        $availableAnnualLeave = max(0, $employee->annual_leave_allowance - $usedAnnualLeave);

        return view('profile.show', compact('employee', 'upcomingShifts', 'currentRequests', 'pastLeaves', 'usedAnnualLeave', 'availableAnnualLeave'));
    }

    public function requestLeave(Request $request)
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Your account is not linked to an employee record.');

        $data = $request->validate([
            'leave_type' => ['required', Rule::in(['Annual leave', 'Personal leave', 'Unpaid leave', 'Other'])],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $employee->leaveRequests()->create($data);

        return back()->with('success', 'Your leave request has been sent for approval.');
    }
}
