<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeaveRequestController extends Controller
{
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
