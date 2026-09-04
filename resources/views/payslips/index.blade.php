@extends('layouts.app')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
        <div><p class="text-sm font-semibold text-blue-600">Payroll</p><h1 class="text-3xl font-bold tracking-tight text-slate-950">Payslips</h1><p class="mt-1 text-sm text-slate-500">Clocked time less recorded breaks, with award penalties and weekly overtime.</p></div>
        <form method="GET" class="flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
            <label class="text-xs font-semibold text-slate-600">From<input class="mt-1 block rounded-lg border-slate-300" type="date" name="start_date" value="{{ $start->toDateString() }}"></label>
            <label class="text-xs font-semibold text-slate-600">To<input class="mt-1 block rounded-lg border-slate-300" type="date" name="end_date" value="{{ $end->toDateString() }}"></label>
            <label class="text-xs font-semibold text-slate-600">Company<select class="mt-1 block rounded-lg border-slate-300" name="company_id"><option value="">All companies</option>@foreach($companies as $company)<option value="{{ $company->id }}" @selected(request('company_id') == $company->id)>{{ $company->name }}</option>@endforeach</select></label>
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Apply</button>
        </form>
    </div>
    <form method="POST" action="{{ route('payslips.email') }}">@csrf
        <input type="hidden" name="start_date" value="{{ $start->toDateString() }}"><input type="hidden" name="end_date" value="{{ $end->toDateString() }}">
        <div class="mb-3 flex items-center justify-between"><p class="text-sm text-slate-500">{{ $start->format('d M Y') }} – {{ $end->format('d M Y') }}</p><button class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Email selected</button></div>
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"><table class="w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="p-4"><input type="checkbox" onclick="document.querySelectorAll('[name=\'employee_ids[]\']').forEach(x=>x.checked=this.checked)" aria-label="Select all"></th><th class="p-4">Employee</th><th class="p-4">Hours</th><th class="p-4">Gross pay</th><th class="p-4 text-right">Actions</th></tr></thead><tbody class="divide-y divide-slate-100">
            @forelse($payslips as $payslip)<tr><td class="p-4"><input type="checkbox" name="employee_ids[]" value="{{ $payslip['employee']->id }}"></td><td class="p-4"><p class="font-semibold text-slate-900">{{ $payslip['employee']->name }}</p><p class="text-xs text-slate-500">{{ $payslip['employee']->email ?: 'No email address' }}</p></td><td class="p-4">{{ number_format($payslip['total_hours'], 2) }}</td><td class="p-4 font-semibold">${{ number_format($payslip['gross_pay'], 2) }}</td><td class="p-4 text-right"><a class="font-semibold text-blue-600" href="{{ route('payslips.show', ['employee'=>$payslip['employee'], 'start_date'=>$start->toDateString(), 'end_date'=>$end->toDateString()]) }}">View</a></td></tr>@empty<tr><td colspan="5" class="p-10 text-center text-slate-500">No employees found.</td></tr>@endforelse
        </tbody></table></div>
    </form>
</div>
@endsection
