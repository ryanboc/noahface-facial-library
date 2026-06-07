@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Payroll Timesheets</h1>
    </div>

    {{-- FILTER BAR --}}
    <div class="bg-white p-4 rounded-lg shadow-md mb-6">
        <form method="GET" action="{{ route('attendance.timesheet') }}" class="flex flex-wrap items-end gap-4">
            
            {{-- Search Input --}}
            <div class="w-full md:w-1/4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Employee Name</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search name..." 
                       class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            {{-- Date Range --}}
            <div class="w-full md:w-1/6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" 
                       class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="w-full md:w-1/6">
                <label class="block text-gray-700 text-sm font-bold mb-2">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" 
                       class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            {{-- Filter Button --}}
            <div class="w-full md:w-auto">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded focus:outline-none focus:shadow-outline">
                    Filter
                </button>
            </div>

            {{-- Export Button (Preserves current filters) --}}
            <div class="w-full md:w-auto ml-auto">
                <a href="{{ route('attendance.timesheet', array_merge(request()->all(), ['export' => 'true'])) }}" 
                   class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded flex items-center">
                   <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                   Export CSV
                </a>
            </div>
        </form>
    </div>

    {{-- TABLE --}}
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full leading-normal">
    <thead>
        <tr class="bg-gray-800 text-white uppercase text-xs font-semibold">
            <th class="py-3 px-5 text-left">Date</th>
            <th class="py-3 px-5 text-left">Employee</th>
            <th class="py-3 px-5 text-left">Shift Time</th>
            <th class="py-3 px-5 text-left">Duration</th>
            <th class="py-3 px-5 text-left">Details</th> <th class="py-3 px-5 text-left">Rate</th>
            <th class="py-3 px-5 text-left">Total Pay</th>
        </tr>
    </thead>
    <tbody class="text-gray-700">
        @forelse($timesheets as $sheet)
            <tr class="border-b border-gray-200 hover:bg-gray-50">
                <td class="py-4 px-5">{{ $sheet['date'] }}</td>
                <td class="py-4 px-5 font-bold">{{ $sheet['employee'] }}</td>
                <td class="py-4 px-5">
                    {{ $sheet['start'] }} - {{ $sheet['end'] }}
                </td>
                <td class="py-4 px-5">{{ $sheet['duration'] }}</td>
                
                <td class="py-4 px-5">
                    <div class="text-sm text-gray-900 font-semibold">{{ $sheet['device'] }}</div>
                    <div class="text-xs text-gray-500">Method: {{ $sheet['method'] }}</div>
                    @if($sheet['temperature'] !== 'N/A' && $sheet['temperature'] > 0)
                        <div class="text-xs text-red-500">Temp: {{ $sheet['temperature'] }}°C</div>
                    @endif
                </td>
                
                <td class="py-4 px-5">
                    <div class="text-gray-900">{{ $sheet['rate'] }}</div>
                    <div class="text-xs text-gray-500">{{ $sheet['rate_label'] }}</div>
                </td>
                <td class="py-4 px-5 font-bold text-green-600 text-lg">
                    {{ $sheet['total_pay'] }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center py-8 text-gray-500">
                    No shifts found for this period.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
    </div>
</div>
@endsection