@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-7xl px-4 py-8">
    <div class="mb-7">
        <p class="text-sm font-semibold uppercase tracking-wide text-blue-600">Employee profile</p>
        <h1 class="text-3xl font-bold text-gray-900">Welcome, {{ auth()->user()->name }}</h1>
    </div>

    @if(!$employee)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-6 text-amber-900">
            <h2 class="font-bold">Profile not linked</h2>
            <p class="mt-1 text-sm">Ask a manager to create or update your employee record using <strong>{{ auth()->user()->email }}</strong>. Your schedule and leave will appear here automatically.</p>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-3 mb-7">
            <div class="rounded-xl border bg-white p-5"><p class="text-sm text-gray-500">Upcoming shifts</p><p class="mt-1 text-3xl font-bold">{{ $upcomingShifts->count() }}</p></div>
            <div class="rounded-xl border bg-white p-5"><p class="text-sm text-gray-500">Annual leave available</p><p class="mt-1 text-3xl font-bold text-green-700">{{ $availableAnnualLeave }} <span class="text-base font-medium">days</span></p></div>
            <div class="rounded-xl border bg-white p-5"><p class="text-sm text-gray-500">Annual leave used in {{ now()->year }}</p><p class="mt-1 text-3xl font-bold">{{ $usedAnnualLeave }} <span class="text-base font-medium">days</span></p></div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1fr_380px]">
            <div class="space-y-6">
                <section class="rounded-xl border bg-white p-5">
                    <h2 class="text-xl font-bold mb-4">My schedule</h2>
                    <div class="divide-y">@forelse($upcomingShifts as $shift)<div class="py-3 flex justify-between gap-4"><div><p class="font-semibold">{{ $shift->shift_date->format('D, d M Y') }}</p><p class="text-sm text-gray-500">{{ $shift->role ?: 'Scheduled shift' }}{{ $shift->location ? ' · '.$shift->location : '' }}</p></div><p class="font-medium text-blue-700 whitespace-nowrap">{{ \Carbon\Carbon::parse($shift->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($shift->end_time)->format('g:i A') }}</p></div>@empty<p class="py-6 text-center text-gray-500">No upcoming shifts.</p>@endforelse</div>
                </section>

                <section class="rounded-xl border bg-white p-5">
                    <h2 class="text-xl font-bold mb-4">Current leave requests</h2>
                    <div class="space-y-3">@forelse($currentRequests as $leave)<div class="rounded-lg border p-4"><div class="flex justify-between gap-3"><div><p class="font-semibold">{{ $leave->leave_type }}</p><p class="text-sm text-gray-500">{{ $leave->start_date->format('d M Y') }} – {{ $leave->end_date->format('d M Y') }}</p></div><span class="h-fit rounded-full px-2.5 py-1 text-xs font-semibold {{ $leave->status === 'approved' ? 'bg-green-100 text-green-700' : ($leave->status === 'declined' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">{{ ucfirst($leave->status) }}</span></div>@if($leave->manager_note)<p class="mt-2 text-sm text-gray-600">Manager: {{ $leave->manager_note }}</p>@endif</div>@empty<p class="text-gray-500">No current leave requests.</p>@endforelse</div>
                </section>

                <section class="rounded-xl border bg-white p-5">
                    <h2 class="text-xl font-bold mb-4">Past leave</h2>
                    <div class="divide-y">@forelse($pastLeaves as $leave)<div class="py-3 flex justify-between"><div><p class="font-medium">{{ $leave->leave_type }}</p><p class="text-sm text-gray-500">{{ $leave->start_date->format('d M Y') }} – {{ $leave->end_date->format('d M Y') }}</p></div><span class="text-sm capitalize">{{ $leave->status }}</span></div>@empty<p class="text-gray-500">No past leave.</p>@endforelse</div>
                    <div class="mt-4">{{ $pastLeaves->links() }}</div>
                </section>
            </div>

            <section class="rounded-xl border bg-white p-5 h-fit">
                <h2 class="text-xl font-bold">Request leave</h2><p class="text-sm text-gray-500 mb-5">Your manager or an executive will review your request.</p>
                <form method="POST" action="{{ route('profile.leave.store') }}" class="space-y-4">@csrf
                    <div><label class="text-sm font-medium">Leave type</label><select name="leave_type" class="mt-1 w-full rounded-lg border px-3 py-2.5">@foreach(['Annual leave','Personal leave','Unpaid leave','Other'] as $type)<option @selected(old('leave_type') === $type)>{{ $type }}</option>@endforeach</select></div>
                    <div class="grid grid-cols-2 gap-3"><div><label class="text-sm font-medium">From</label><input type="date" name="start_date" min="{{ today()->toDateString() }}" value="{{ old('start_date') }}" required class="mt-1 w-full rounded-lg border px-3 py-2.5"></div><div><label class="text-sm font-medium">To</label><input type="date" name="end_date" min="{{ today()->toDateString() }}" value="{{ old('end_date') }}" required class="mt-1 w-full rounded-lg border px-3 py-2.5"></div></div>
                    <div><label class="text-sm font-medium">Reason <span class="text-gray-400">(optional)</span></label><textarea name="reason" rows="3" class="mt-1 w-full rounded-lg border px-3 py-2.5">{{ old('reason') }}</textarea></div>
                    <button class="w-full rounded-lg bg-blue-600 py-2.5 font-semibold text-white hover:bg-blue-700">Send for approval</button>
                </form>
            </section>
        </div>
    @endif
</div>
@endsection
