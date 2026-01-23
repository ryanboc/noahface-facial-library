@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Employee Management</h1>
        <a href="{{ route('employees.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            + Add Employee
        </a>
    </div>

    <div class="bg-white shadow-md rounded my-6 overflow-x-auto">
        <table class="min-w-full w-full table-auto">
            <thead>
                <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-left">Name / Email</th>
                    <th class="py-3 px-6 text-left">NoahFace ID</th>
                    <th class="py-3 px-6 text-left">Award</th>
                    <th class="py-3 px-6 text-left">Type</th>
                    <th class="py-3 px-6 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm font-light">
                @forelse($employees as $employee)
                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                        <td class="py-3 px-6 text-left">
                            <div class="font-medium">{{ $employee->name }}</div>
                            <div class="text-xs text-gray-500">{{ $employee->email }}</div>
                        </td>
                        <td class="py-3 px-6 text-left">
                            <span class="bg-gray-200 py-1 px-3 rounded text-xs font-bold text-gray-700">
                                {{ $employee->noahface_id }}
                            </span>
                        </td>
                        <td class="py-3 px-6 text-left">
                            {{ $employee->award->name ?? 'No Award Linked' }}
                        </td>
                        <td class="py-3 px-6 text-left">
                            <span class="{{ $employee->employment_type == 'Casual' ? 'text-orange-600' : 'text-blue-600' }} font-bold">
                                {{ $employee->employment_type }}
                            </span>
                        </td>
                        <td class="py-3 px-6 text-center">
                            <div class="flex item-center justify-center space-x-2">
                                <a href="{{ route('employees.edit', $employee) }}" class="text-blue-500 hover:text-blue-700 font-bold">Edit</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">No employees found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $employees->links() }}
</div>
@endsection