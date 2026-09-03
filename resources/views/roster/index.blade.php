@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-[1500px]">
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-7">
        <div><p class="text-sm font-semibold uppercase tracking-wide text-blue-600">Manager workspace</p><h1 class="text-3xl font-bold text-gray-900">Weekly roster</h1><p class="mt-1 text-gray-500">{{ $selectedCompany?->name ?? 'No company selected' }} · {{ $weekStart->format('d M') }} – {{ $weekEnd->format('d M Y') }}</p></div>
        <div class="flex flex-wrap gap-2"><a href="{{ route('roster.index', ['week' => $weekStart->copy()->subWeek()->toDateString(), 'company_id' => $selectedCompany?->id]) }}" class="px-3 py-2 bg-white border rounded-lg">← Previous</a><a href="{{ route('roster.index', ['company_id' => $selectedCompany?->id]) }}" class="px-3 py-2 bg-white border rounded-lg">This week</a><a href="{{ route('roster.index', ['week' => $weekStart->copy()->addWeek()->toDateString(), 'company_id' => $selectedCompany?->id]) }}" class="px-3 py-2 bg-white border rounded-lg">Next →</a><a href="{{ route('roster.pdf', ['week' => $weekStart->toDateString(), 'company_id' => $selectedCompany?->id]) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold">Download PDF</a><a target="_blank" href="{{ route('roster.print', ['week' => $weekStart->toDateString(), 'company_id' => $selectedCompany?->id]) }}" class="px-4 py-2 bg-gray-900 text-white rounded-lg font-semibold">Print roster</a></div>
    </div>

    <form method="GET" action="{{ route('roster.index') }}" class="mb-6 rounded-xl border bg-white p-4 shadow-sm flex flex-col gap-3 sm:flex-row sm:items-end"><input type="hidden" name="week" value="{{ $weekStart->toDateString() }}"><div class="w-full sm:max-w-sm"><label for="roster-company" class="block text-sm font-semibold mb-1">Company / workplace</label><select id="roster-company" name="company_id" onchange="this.form.submit()" class="w-full rounded-lg border px-3 py-2.5">@foreach($companies as $company)<option value="{{ $company->id }}" @selected($selectedCompany?->id === $company->id)>{{ $company->name }}</option>@endforeach</select></div><p class="pb-2 text-sm text-gray-500">Only employees assigned to this company are shown.</p></form>

    <div class="grid sm:grid-cols-3 gap-4 mb-6"><div class="bg-white border rounded-xl p-4"><p class="text-sm text-gray-500">Scheduled shifts</p><p class="text-2xl font-bold">{{ $shifts->count() }}</p></div><div class="bg-white border rounded-xl p-4"><p class="text-sm text-gray-500">Rostered hours</p><p class="text-2xl font-bold">{{ number_format($totalHours, 1) }}</p></div><div class="bg-white border rounded-xl p-4"><p class="text-sm text-gray-500">People on leave</p><p class="text-2xl font-bold">{{ $leave->pluck('employee_id')->unique()->count() }}</p></div></div>

    <form method="POST" action="{{ route('roster.send-weekly') }}" class="mb-6 rounded-xl border border-green-200 bg-green-50 p-5 shadow-sm" onsubmit="return confirm('Send this weekly roster to every rostered employee with a mobile number?');">
        @csrf
        <input type="hidden" name="week" value="{{ $weekStart->toDateString() }}">
        <input type="hidden" name="company_id" value="{{ $selectedCompany?->id }}">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="font-bold text-green-950">Send weekly roster by SMS</h2>
                <p class="mt-1 text-sm text-green-800">{{ $rosteredEmployees->count() }} rostered {{ Str::plural('employee', $rosteredEmployees->count()) }} · {{ $rosteredEmployees->whereNull('phone')->count() }} missing {{ Str::plural('mobile number', $rosteredEmployees->whereNull('phone')->count()) }}</p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div><label for="roster-message-template" class="mb-1 block text-sm font-semibold">Message template <span class="font-normal text-gray-500">(optional)</span></label><select id="roster-message-template" name="message_template_id" class="min-w-64 rounded-lg border bg-white px-3 py-2.5"><option value="">Standard roster message</option>@foreach($messageTemplates as $template)<option value="{{ $template->id }}">{{ $template->name }}</option>@endforeach</select></div>
                <button type="submit" @disabled($shifts->isEmpty() || !$selectedCompany) class="rounded-lg bg-green-600 px-5 py-2.5 font-semibold text-white hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50">Send to everyone rostered</button>
            </div>
        </div>
        <p class="mt-3 text-xs text-green-800">Templates support <code>{name}</code>, <code>{week}</code>, <code>{company}</code>, and <code>{roster}</code>. The employee's schedule is appended automatically if <code>{roster}</code> is omitted.</p>
    </form>

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
        <aside class="bg-white border rounded-xl shadow-sm p-5 h-fit"><h2 class="font-bold text-lg">Add a shift</h2><p class="text-sm text-gray-500 mb-5">Leave, overlapping shifts and weekly hours are checked automatically.</p><form method="POST" action="{{ route('roster.store') }}" class="space-y-4">@csrf
            <input type="hidden" name="company_id" value="{{ $selectedCompany?->id }}">
            @php $selectedRosterDate = old('shift_date', $weekStart->toDateString()); @endphp
            <div><label class="text-sm font-medium">Employee</label><select id="roster-employee" name="employee_id" required class="mt-1 w-full border rounded-lg px-3 py-2.5"><option value="">{{ $employees->isEmpty() ? 'No employees assigned to this company' : 'Select employee' }}</option>@foreach($employees as $employee)@php $optionLabel = $employee->name.($employeeHours->has($employee->id) ? ' — '.number_format($employeeHours[$employee->id], 1).' hrs' : ''); $isOnLeave = $leave->where('employee_id', $employee->id)->contains(fn($item) => \Carbon\Carbon::parse($selectedRosterDate)->between($item->start_date, $item->end_date)); @endphp<option value="{{ $employee->id }}" data-label="{{ $optionLabel }}" @selected(old('employee_id') == $employee->id) @disabled($isOnLeave)>{{ $optionLabel }}{{ $isOnLeave ? ' — On approved leave' : '' }}</option>@endforeach</select><p id="leave-selection-help" class="mt-1 text-xs text-amber-700 hidden">Employees on approved leave cannot be selected for this date.</p>@error('employee_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
            <div><label class="text-sm font-medium">Date</label><input id="roster-date" type="date" name="shift_date" value="{{ $selectedRosterDate }}" required class="mt-1 w-full border rounded-lg px-3 py-2.5"></div>
            <div class="grid grid-cols-2 gap-3"><div><label class="text-sm font-medium">Start</label><input type="time" name="start_time" value="{{ old('start_time', '09:00') }}" required class="mt-1 w-full border rounded-lg px-3 py-2.5">@error('start_time')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div><div><label class="text-sm font-medium">End</label><input type="time" name="end_time" value="{{ old('end_time', '17:00') }}" required class="mt-1 w-full border rounded-lg px-3 py-2.5">@error('end_time')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div></div>
            <div class="grid grid-cols-2 gap-3"><div><label class="text-sm font-medium">Role</label><input name="role" value="{{ old('role') }}" class="mt-1 w-full border rounded-lg px-3 py-2.5" placeholder="Supervisor"></div><div><label class="text-sm font-medium">Location</label><input name="location" value="{{ old('location') }}" class="mt-1 w-full border rounded-lg px-3 py-2.5" placeholder="Main site"></div></div>
            <div><label class="text-sm font-medium">Notes</label><textarea name="notes" rows="2" class="mt-1 w-full border rounded-lg px-3 py-2.5">{{ old('notes') }}</textarea></div><p class="text-xs text-gray-500">A warning is shown above 38 weekly hours. Shifts exceeding 40 hours cannot be added.</p><button class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-lg py-2.5 font-semibold">Add shift</button>
        </form></aside>
    </div>
</div>
<script>
    const approvedLeave = @json($leave->map(fn($item) => ['employee_id' => (string) $item->employee_id, 'start' => $item->start_date->toDateString(), 'end' => $item->end_date->toDateString()])->values());
    const rosterDate = document.getElementById('roster-date');
    const rosterEmployee = document.getElementById('roster-employee');
    const leaveSelectionHelp = document.getElementById('leave-selection-help');

    function updateEmployeeAvailability() {
        const selectedDate = rosterDate.value;
        let unavailableCount = 0;

        Array.from(rosterEmployee.options).forEach(option => {
            if (!option.value) return;
            const isOnLeave = approvedLeave.some(leave => leave.employee_id === option.value && selectedDate >= leave.start && selectedDate <= leave.end);
            option.disabled = isOnLeave;
            option.textContent = option.dataset.label + (isOnLeave ? ' — On approved leave' : '');
            if (isOnLeave) unavailableCount++;
        });

        if (rosterEmployee.selectedOptions[0]?.disabled) rosterEmployee.value = '';
        leaveSelectionHelp.classList.toggle('hidden', unavailableCount === 0);
    }

    rosterDate.addEventListener('change', updateEmployeeAvailability);
    updateEmployeeAvailability();
</script>
@endsection
