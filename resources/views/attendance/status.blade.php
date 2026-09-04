@extends('layouts.app')

@section('content')
@php
    $sections = [
        'clocked_in' => ['title' => 'Clocked in', 'colour' => 'emerald', 'empty' => 'Nobody is clocked in.'],
        'on_break' => ['title' => 'On break', 'colour' => 'amber', 'empty' => 'Nobody is on break.'],
        'clocked_out' => ['title' => 'Clocked out', 'colour' => 'slate', 'empty' => 'Nobody has clocked out today.'],
        'not_clocked_in' => ['title' => 'Not clocked in today', 'colour' => 'gray', 'empty' => 'Everyone has recorded activity today.'],
    ];
@endphp

<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-blue-600">Live attendance</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">Who's working</h1>
            <p class="mt-2 text-sm text-slate-500">Latest attendance status for {{ now()->format('l, j F Y') }}. This page refreshes every minute.</p>
        </div>

        <form method="GET" action="{{ route('attendance.status') }}" class="flex items-end gap-2">
            <label class="block">
                <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Company</span>
                <select name="company_id" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    <option value="">All companies</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" @selected($companyId === $company->id)>{{ $company->name }}</option>
                    @endforeach
                </select>
            </label>
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Filter</button>
        </form>
    </div>

    <div class="mb-7 grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach($sections as $key => $section)
            <a href="#{{ $key }}" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="text-3xl font-bold text-{{ $section['colour'] }}-600">{{ $groups[$key]->count() }}</div>
                <div class="mt-1 text-sm font-semibold text-slate-600">{{ $section['title'] }}</div>
            </a>
        @endforeach
    </div>

    @if($selectedCompany)
        <p class="mb-4 text-sm text-slate-500">Showing employees from <span class="font-semibold text-slate-800">{{ $selectedCompany->name }}</span>.</p>
    @endif

    <details id="correction" class="mb-6 rounded-2xl border border-blue-200 bg-blue-50/60 p-5" @if($errors->any()) open @endif>
        <summary class="cursor-pointer font-bold text-blue-900">Add attendance correction</summary>
        <p class="mt-2 text-sm text-blue-700">Use this when someone forgot an event. A new audited event is added; device records are never overwritten.</p>
        <form method="POST" action="{{ route('attendance.adjustments.store') }}" class="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            @csrf
            <input type="hidden" name="return_to" value="status">
            <label class="block"><span class="mb-1 block text-xs font-bold text-slate-600">Employee</span><select name="employee_id" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm"><option value="">Select employee</option>@foreach($employees as $employee)<option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>{{ $employee->name }}</option>@endforeach</select></label>
            <label class="block"><span class="mb-1 block text-xs font-bold text-slate-600">Event</span><select name="event_type" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">@foreach(['Clock In', 'Start Break', 'End Break', 'Clock Out'] as $event)<option @selected(old('event_type') === $event)>{{ $event }}</option>@endforeach</select></label>
            <label class="block"><span class="mb-1 block text-xs font-bold text-slate-600">Date and time</span><input type="datetime-local" name="clock_time" value="{{ old('clock_time', now()->format('Y-m-d\TH:i')) }}" max="{{ now()->format('Y-m-d\TH:i') }}" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm"></label>
            <label class="block"><span class="mb-1 block text-xs font-bold text-slate-600">Reason</span><input name="adjustment_reason" value="{{ old('adjustment_reason') }}" minlength="5" maxlength="1000" required placeholder="e.g. Employee forgot to clock out" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm"></label>
            <div class="lg:col-span-4"><button class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-bold text-white hover:bg-blue-700">Add correction</button></div>
        </form>
    </details>

    <div class="grid gap-6 lg:grid-cols-2">
        @foreach($sections as $key => $section)
            <section id="{{ $key }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <header class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div class="flex items-center gap-3">
                        <span class="h-3 w-3 rounded-full bg-{{ $section['colour'] }}-500"></span>
                        <h2 class="font-bold text-slate-900">{{ $section['title'] }}</h2>
                    </div>
                    <span class="rounded-full bg-{{ $section['colour'] }}-50 px-2.5 py-1 text-xs font-bold text-{{ $section['colour'] }}-700">{{ $groups[$key]->count() }}</span>
                </header>

                <div class="divide-y divide-slate-100">
                    @forelse($groups[$key] as $item)
                        <div class="flex items-center gap-3 px-5 py-4">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-slate-100 text-sm font-bold text-slate-600">{{ strtoupper(substr($item->employee->name, 0, 1)) }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold text-slate-900">{{ $item->employee->name }}</p>
                                <p class="truncate text-xs text-slate-500">{{ $item->employee->companies->pluck('name')->join(', ') ?: 'No company assigned' }}</p>
                                @if($item->warning)<p class="mt-1 text-xs font-semibold text-red-600">Needs review: {{ $item->warning }}</p>@endif
                            </div>
                            <div class="shrink-0 text-right">
                                @if($item->latestLog)
                                    <p class="text-sm font-semibold text-slate-700">{{ $item->latestLog->clock_time->format('g:i A') }}</p>
                                    <p class="text-xs text-slate-400">
                                        {{ $item->latestLog->location ?: $item->latestLog->event_type }}
                                        @if($item->latestLog->is_manual) · corrected @endif
                                    </p>
                                @else
                                    <p class="text-xs text-slate-400">No activity</p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="px-5 py-8 text-center text-sm text-slate-400">{{ $section['empty'] }}</p>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
</div>

<script>
    if (window.location.hash === '#correction') document.getElementById('correction')?.setAttribute('open', '');
    setTimeout(function () { window.location.reload(); }, 60000);
</script>
@endsection
