@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-7xl">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between mb-7">
        <div><p class="text-sm font-semibold uppercase tracking-wide text-blue-600">People management</p><h1 class="text-3xl font-bold text-gray-900">Leave approvals</h1><p class="mt-1 text-gray-500">Review requests and keep availability accurate for rostering.</p></div>
        <div class="flex gap-2">@foreach(['' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'declined' => 'Declined'] as $value => $label)<a href="{{ route('leave.index', $value ? ['status' => $value] : []) }}" class="px-3 py-2 rounded-lg text-sm font-medium {{ $status === $value ? 'bg-gray-900 text-white' : 'bg-white border text-gray-600' }}">{{ $label }}</a>@endforeach</div>
    </div>

    <div class="grid lg:grid-cols-[340px_1fr] gap-6">
        <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 h-fit">
            <h2 class="font-bold text-lg">New leave request</h2><p class="text-sm text-gray-500 mb-5">Record a request on behalf of an employee.</p>
            <form method="POST" action="{{ route('leave.store') }}" class="space-y-4">@csrf
                <div><label class="text-sm font-medium">Employee</label><select name="employee_id" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2.5"><option value="">Select employee</option>@foreach($employees as $employee)<option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>{{ $employee->name }}</option>@endforeach</select></div>
                <div><label class="text-sm font-medium">Leave type</label><select name="leave_type" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2.5">@foreach(['Annual leave','Personal leave','Unpaid leave','Other'] as $type)<option @selected(old('leave_type') === $type)>{{ $type }}</option>@endforeach</select></div>
                <div class="grid grid-cols-2 gap-3"><div><label class="text-sm font-medium">From</label><input type="date" name="start_date" value="{{ old('start_date') }}" required class="mt-1 w-full rounded-lg border px-3 py-2.5"></div><div><label class="text-sm font-medium">To</label><input type="date" name="end_date" value="{{ old('end_date') }}" required class="mt-1 w-full rounded-lg border px-3 py-2.5"></div></div>
                <div><label class="text-sm font-medium">Reason <span class="text-gray-400">(optional)</span></label><textarea name="reason" rows="3" class="mt-1 w-full rounded-lg border px-3 py-2.5" placeholder="Add context for the manager">{{ old('reason') }}</textarea></div>
                <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg py-2.5">Submit request</button>
            </form>
        </section>

        <section class="space-y-3">
            @forelse($requests as $leaveRequest)
            <article class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <div class="flex flex-col sm:flex-row sm:justify-between gap-3">
                    <div><div class="flex items-center gap-2"><h3 class="font-bold text-gray-900">{{ $leaveRequest->employee->name }}</h3><span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $leaveRequest->status === 'approved' ? 'bg-green-100 text-green-700' : ($leaveRequest->status === 'declined' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">{{ ucfirst($leaveRequest->status) }}</span></div><p class="text-sm text-gray-600 mt-1">{{ $leaveRequest->leave_type }} · {{ $leaveRequest->start_date->format('d M Y') }} – {{ $leaveRequest->end_date->format('d M Y') }} · {{ $leaveRequest->start_date->diffInDays($leaveRequest->end_date) + 1 }} day(s)</p>@if($leaveRequest->reason)<p class="mt-3 text-sm text-gray-700">{{ $leaveRequest->reason }}</p>@endif</div>
                    <p class="text-xs text-gray-400 whitespace-nowrap">Requested {{ $leaveRequest->created_at->diffForHumans() }}</p>
                </div>
                @if($leaveRequest->status === 'pending')
                <form method="POST" action="{{ route('leave.review', $leaveRequest) }}" class="mt-4 pt-4 border-t flex flex-col sm:flex-row gap-2">@csrf @method('PATCH')<input name="manager_note" class="flex-1 rounded-lg border px-3 py-2 text-sm" placeholder="Manager note (optional)"><button name="status" value="approved" class="px-4 py-2 rounded-lg bg-green-600 text-white text-sm font-semibold">Approve</button><button name="status" value="declined" class="px-4 py-2 rounded-lg bg-white border border-red-200 text-red-700 text-sm font-semibold">Decline</button></form>
                @elseif($leaveRequest->manager_note)<p class="mt-4 pt-4 border-t text-sm text-gray-500"><span class="font-medium">Manager note:</span> {{ $leaveRequest->manager_note }}</p>@endif
            </article>
            @empty <div class="bg-white border rounded-xl p-12 text-center text-gray-500">No leave requests match this view.</div>@endforelse
            {{ $requests->links() }}
        </section>
    </div>
</div>
@endsection
