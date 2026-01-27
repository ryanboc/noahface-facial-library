@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Payroll Timesheets</h1>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full leading-normal">
            <thead>
                <tr class="bg-gray-800 text-white uppercase text-xs font-semibold">
                    <th class="py-3 px-5 text-left">Date</th>
                    <th class="py-3 px-5 text-left">Employee</th>
                    <th class="py-3 px-5 text-left">Shift Time</th>
                    <th class="py-3 px-5 text-left">Hours</th>
                    <th class="py-3 px-5 text-left">Rate</th>
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
                        <td class="py-4 px-5 text-gray-500">{{ $sheet['rate'] }}</td>
                        <td class="py-4 px-5 font-bold text-green-600 text-lg">
                            {{ $sheet['total_pay'] }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">No completed shifts found (Need Clock In + Clock Out pair)</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection