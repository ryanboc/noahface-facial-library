<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeaveRequestController extends Controller
{
    public function calendar(Request $request)
    {
        $request->validate(['company_id' => ['nullable', 'integer', 'exists:companies,id']]);
        $companyId = $request->integer('company_id') ?: null;
        $companies = Company::orderBy('name')->get();

        try {
            $month = Carbon::createFromFormat('Y-m', $request->string('month', now()->format('Y-m'))->toString())->startOfMonth();
        } catch (\Throwable) {
            $month = now()->startOfMonth();
        }

        $calendarStart = $month->copy()->startOfWeek();
        $calendarEnd = $month->copy()->endOfMonth()->endOfWeek();
        $calendarDays = collect(range(0, $calendarStart->diffInDays($calendarEnd)))
            ->map(fn ($offset) => $calendarStart->copy()->addDays($offset));

        $approvedLeave = LeaveRequest::with('employee.companies')
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $calendarEnd)
            ->whereDate('end_date', '>=', $calendarStart)
            ->when($companyId, fn ($query) => $query->whereHas('employee.companies', fn ($query) => $query->whereKey($companyId)))
            ->orderBy('start_date')
            ->get();

        return view('leave.calendar', compact('month', 'calendarDays', 'approvedLeave', 'companies', 'companyId'));
    }

    public function index(Request $request)
    {
        abort_unless($request->user()->canApproveLeave(), 403);
        $status = $request->string('status')->toString();
        $requests = LeaveRequest::with(['employee', 'reviewer'])
            ->when(in_array($status, ['pending', 'approved', 'declined']), fn ($query) => $query->where('status', $status))
            ->orderByRaw("case when status = 'pending' then 0 else 1 end")
            ->latest('start_date')->paginate(15)->withQueryString();

        return view('leave.index', ['requests' => $requests, 'employees' => Employee::orderBy('name')->get(), 'status' => $status]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->canApproveLeave(), 403);
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'leave_type' => ['required', Rule::in(['Annual leave', 'Personal leave', 'Unpaid leave', 'Other'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);
        LeaveRequest::create($data);

        return back()->with('success', 'Leave request submitted for approval.');
    }

    public function review(Request $request, LeaveRequest $leaveRequest)
    {
        abort_unless($request->user()->canApproveLeave(), 403);
        $data = $request->validate([
            'status' => ['required', Rule::in(['approved', 'declined'])],
            'manager_note' => ['nullable', 'string', 'max:1000'],
        ]);
        $leaveRequest->update($data + ['reviewed_by' => $request->user()->id, 'reviewed_at' => now()]);

        return back()->with('success', "Leave request {$data['status']}.");
    }
}
