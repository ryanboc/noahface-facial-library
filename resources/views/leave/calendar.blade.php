@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-[1500px] px-4 py-8">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div><p class="text-sm font-semibold uppercase tracking-wide text-blue-600">People management</p><h1 class="text-3xl font-bold text-gray-900">Approved leave calendar</h1><p class="mt-1 text-gray-500">{{ $month->format('F Y') }}</p></div>
        <div class="flex flex-wrap gap-2"><a href="{{ route('leave.index') }}" class="rounded-lg border bg-white px-3 py-2 text-sm font-medium">Leave approvals</a><a href="{{ route('leave.calendar', ['month' => $month->copy()->subMonth()->format('Y-m'), 'company_id' => $companyId]) }}" class="rounded-lg border bg-white px-3 py-2">← Previous</a><a href="{{ route('leave.calendar', ['company_id' => $companyId]) }}" class="rounded-lg border bg-white px-3 py-2">This month</a><a href="{{ route('leave.calendar', ['month' => $month->copy()->addMonth()->format('Y-m'), 'company_id' => $companyId]) }}" class="rounded-lg border bg-white px-3 py-2">Next →</a></div>
    </div>

    <form method="GET" action="{{ route('leave.calendar') }}" class="mb-5 flex flex-col gap-3 rounded-xl border bg-white p-4 shadow-sm sm:flex-row sm:items-end"><input type="hidden" name="month" value="{{ $month->format('Y-m') }}"><div class="w-full sm:max-w-sm"><label for="leave-calendar-company" class="mb-1 block text-sm font-semibold">Company / Workplace</label><select id="leave-calendar-company" name="company_id" onchange="this.form.submit()" class="w-full rounded-lg border px-3 py-2.5"><option value="">All companies</option>@foreach($companies as $company)<option value="{{ $company->id }}" @selected($companyId === $company->id)>{{ $company->name }}</option>@endforeach</select></div><p class="pb-2 text-sm text-gray-500">Only approved leave is displayed.</p></form>

    <div class="overflow-x-auto rounded-xl border bg-white shadow-sm">
        <div class="grid min-w-[1050px] grid-cols-7 bg-gray-100">@foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $dayName)<div class="border-r px-3 py-3 text-center text-xs font-bold uppercase tracking-wide text-gray-600 last:border-r-0">{{ $dayName }}</div>@endforeach</div>
        <div class="grid min-w-[1050px] grid-cols-7">
            @foreach($calendarDays as $day)
                @php($dayLeave = $approvedLeave->filter(fn ($leave) => $day->between($leave->start_date, $leave->end_date)))
                <div class="min-h-36 border-r border-t p-2 last:border-r-0 {{ $day->month !== $month->month ? 'bg-gray-50 text-gray-400' : 'bg-white' }}">
                    <div class="mb-2 flex justify-between"><span class="text-sm font-bold {{ $day->isToday() ? 'flex h-7 w-7 items-center justify-center rounded-full bg-blue-600 text-white' : '' }}">{{ $day->day }}</span><span class="text-xs text-gray-400">{{ $dayLeave->count() ?: '' }}</span></div>
                    <div class="space-y-1.5">@foreach($dayLeave as $leave)<div class="rounded-md border border-blue-200 bg-blue-50 px-2 py-1.5 text-xs text-blue-900" title="{{ $leave->start_date->format('d M') }} – {{ $leave->end_date->format('d M Y') }}"><p class="truncate font-bold">{{ $leave->employee->name }}</p><p class="truncate text-blue-700">{{ $leave->leave_type }}</p></div>@endforeach</div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
