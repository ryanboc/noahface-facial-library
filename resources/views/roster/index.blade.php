@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-[1500px]">
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-7">
        <div><p class="text-sm font-semibold uppercase tracking-wide text-blue-600">Manager workspace</p><h1 class="text-3xl font-bold text-gray-900">Weekly roster</h1><p class="mt-1 text-gray-500">{{ $weekStart->format('d M') }} – {{ $weekEnd->format('d M Y') }}</p></div>
        <div class="flex flex-wrap gap-2"><a href="{{ route('roster.index', ['week' => $weekStart->copy()->subWeek()->toDateString()]) }}" class="px-3 py-2 bg-white border rounded-lg">← Previous</a><a href="{{ route('roster.index') }}" class="px-3 py-2 bg-white border rounded-lg">This week</a><a href="{{ route('roster.index', ['week' => $weekStart->copy()->addWeek()->toDateString()]) }}" class="px-3 py-2 bg-white border rounded-lg">Next →</a><a target="_blank" href="{{ route('roster.print', ['week' => $weekStart->toDateString()]) }}" class="px-4 py-2 bg-gray-900 text-white rounded-lg font-semibold">Print roster</a></div>
    </div>

    <div class="grid sm:grid-cols-3 gap-4 mb-6"><div class="bg-white border rounded-xl p-4"><p class="text-sm text-gray-500">Scheduled shifts</p><p class="text-2xl font-bold">{{ $shifts->count() }}</p></div><div class="bg-white border rounded-xl p-4"><p class="text-sm text-gray-500">Rostered hours</p><p class="text-2xl font-bold">{{ number_format($totalHours, 1) }}</p></div><div class="bg-white border rounded-xl p-4"><p class="text-sm text-gray-500">People on leave</p><p class="text-2xl font-bold">{{ $leave->pluck('employee_id')->unique()->count() }}</p></div></div>

    <div class="grid xl:grid-cols-[1fr_330px] gap-6">
        <div class="bg-white border rounded-xl shadow-sm overflow-x-auto">
            <div class="grid grid-cols-7 min-w-[980px]">
                @foreach($days as $day)<div class="border-r last:border-r-0 bg-gray-50 px-3 py-3 text-center"><p class="text-xs uppercase font-semibold text-gray-500">{{ $day->format('D') }}</p><p class="font-bold {{ $day->isToday() ? 'text-blue-600' : '' }}">{{ $day->format('d M') }}</p></div>@endforeach
                @foreach($days as $day)<div class="border-r last:border-r-0 border-t p-2 min-h-[360px] space-y-2">
                    @foreach($leave->filter(fn($item) => $day->between($item->start_date, $item->end_date)) as $item)<div class="rounded-lg bg-amber-50 border border-amber-200 p-2 text-xs text-amber-800"><strong>{{ $item->employee->name }}</strong><br>On leave</div>@endforeach
                    @foreach($shifts->filter(fn($shift) => $shift->shift_date->isSameDay($day)) as $shift)<div class="rounded-lg bg-blue-50 border border-blue-200 p-2.5 text-sm"><div class="flex justify-between gap-1"><strong class="text-blue-950">{{ $shift->employee->name }}</strong><form method="POST" action="{{ route('roster.destroy', $shift) }}" onsubmit="return confirm('Remove this shift?')">@csrf @method('DELETE')<button class="text-gray-400 hover:text-red-600" title="Remove">×</button></form></div><p class="font-semibold text-blue-700">{{ \Carbon\Carbon::parse($shift->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($shift->end_time)->format('g:i A') }}</p>@if($shift->role || $shift->location)<p class="text-xs text-gray-500 mt-1">{{ collect([$shift->role, $shift->location])->filter()->join(' · ') }}</p>@endif</div>@endforeach
                </div>@endforeach
            </div>
        </div>
        <aside class="bg-white border rounded-xl shadow-sm p-5 h-fit"><h2 class="font-bold text-lg">Add a shift</h2><p class="text-sm text-gray-500 mb-5">Leave, duplicate days and weekly hours are checked automatically.</p><form method="POST" action="{{ route('roster.store') }}" class="space-y-4">@csrf
            <div><label class="text-sm font-medium">Employee</label><select name="employee_id" required class="mt-1 w-full border rounded-lg px-3 py-2.5"><option value="">Select employee</option>@foreach($employees as $employee)<option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>{{ $employee->name }}{{ $employeeHours->has($employee->id) ? ' — '.number_format($employeeHours[$employee->id], 1).' hrs' : '' }}</option>@endforeach</select>@error('employee_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
            <div><label class="text-sm font-medium">Date</label><input type="date" name="shift_date" value="{{ old('shift_date', $weekStart->toDateString()) }}" required class="mt-1 w-full border rounded-lg px-3 py-2.5"></div>
            <div class="grid grid-cols-2 gap-3"><div><label class="text-sm font-medium">Start</label><input type="time" name="start_time" value="{{ old('start_time', '09:00') }}" required class="mt-1 w-full border rounded-lg px-3 py-2.5"></div><div><label class="text-sm font-medium">End</label><input type="time" name="end_time" value="{{ old('end_time', '17:00') }}" required class="mt-1 w-full border rounded-lg px-3 py-2.5">@error('end_time')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div></div>
            <div class="grid grid-cols-2 gap-3"><div><label class="text-sm font-medium">Role</label><input name="role" value="{{ old('role') }}" class="mt-1 w-full border rounded-lg px-3 py-2.5" placeholder="Supervisor"></div><div><label class="text-sm font-medium">Location</label><input name="location" value="{{ old('location') }}" class="mt-1 w-full border rounded-lg px-3 py-2.5" placeholder="Main site"></div></div>
            <div><label class="text-sm font-medium">Notes</label><textarea name="notes" rows="2" class="mt-1 w-full border rounded-lg px-3 py-2.5">{{ old('notes') }}</textarea></div><p class="text-xs text-gray-500">A warning is shown above 38 weekly hours. Shifts exceeding 40 hours cannot be added.</p><button class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-lg py-2.5 font-semibold">Add shift</button>
        </form></aside>
    </div>
</div>
@endsection
