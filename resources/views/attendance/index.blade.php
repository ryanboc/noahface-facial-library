@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Attendance Logs</h1>
        <div class="text-sm text-gray-500">
            Showing latest records
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full leading-normal">
            <thead>
                <tr class="bg-gray-100 text-gray-600 uppercase text-xs font-semibold tracking-wider">
                    <th class="py-3 px-5 text-left">Date & Time</th>
                    <th class="py-3 px-5 text-left">Employee</th>
                    <th class="py-3 px-5 text-left">Award / Rate</th>
                    <th class="py-3 px-5 text-left">Event</th>
                    <th class="py-3 px-5 text-left">Location</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                @forelse($logs as $log)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="py-4 px-5">
                            <div class="font-bold text-blue-600">
                                {{ $log->clock_time->format('D, d M Y') }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $log->clock_time->format('h:i:s A') }}
                            </div>
                        </td>

                        <td class="py-4 px-5">
                            @if($log->employee)
                                <div class="font-medium text-gray-900">{{ $log->employee->name }}</div>
                                <div class="text-xs text-gray-400">ID: {{ $log->employee->noahface_id }}</div>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Unknown ID
                                </span>
                            @endif
                        </td>

                        <!-- <td class="py-4 px-5 text-sm">
                            @if($log->employee && $log->employee->award)
                                <div class="text-gray-900">{{ $log->employee->award->name }}</div>
                                <div class="text-xs text-green-600 font-bold">
                                    {{ $log->employee->getRateForDate($log->clock_time) }}
                                </div>
                            @else
                                <span class="text-gray-400 italic">-</span>
                            @endif
                        </td> -->

                        <td class="py-4 px-5">
                            @if($log->employee && $log->employee->award)
                                @php
                                    // CALL THE FUNCTION YOU WROTE IN EMPLOYEE MODEL
                                    $rateInfo = $log->employee->getRateDetails($log->clock_time);
                                @endphp

                                <div class="font-bold text-green-700 text-lg">
                                    ${{ number_format($rateInfo['final_rate'], 2) }} <span class="text-sm font-normal text-gray-500">/hr</span>
                                </div>

                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $rateInfo['label'] }}
                                </div>
                                
                                <div class="text-[10px] text-gray-400 mt-1">
                                    {{ $log->employee->award->name }}
                                </div>
                            @else
                                <span class="text-gray-400 italic">No Award Linked</span>
                            @endif
                        </td>

                        <td class="py-4 px-5">
                            @php
                                $color = match(strtolower($log->event_type)) {
                                    'clock in' => 'green',
                                    'clock out' => 'gray',
                                    'start break' => 'orange',
                                    'end break' => 'blue',
                                    default => 'gray'
                                };
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-bold bg-{{ $color }}-100 text-{{ $color }}-800">
                                {{ ucfirst($log->event_type) }}
                            </span>
                        </td>

                        <td class="py-4 px-5 text-sm text-gray-500">
                            {{ $log->location ?? 'Unknown' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-gray-400">
                            No attendance records found yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>
@endsection