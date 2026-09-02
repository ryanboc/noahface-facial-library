@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Employee Management</h1>
        <a href="{{ route('employees.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            + Add Employee
        </a>
    </div>

    <form method="GET" action="{{ route('employees.index') }}" class="bg-white shadow-md rounded-t mt-6 p-4 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="w-full sm:max-w-md">
            <label for="employee-search" class="block text-sm font-medium text-gray-700 mb-1">Search employees</label>
            <div class="flex gap-2">
                <input id="employee-search" name="search" type="search" value="{{ request('search') }}"
                    placeholder="Name, email, ID, award or type"
                    class="w-full rounded border-gray-300 border px-3 py-2 focus:border-blue-500 focus:ring-blue-500">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded">Search</button>
                @if(request()->filled('search'))
                    <a href="{{ route('employees.index', ['per_page' => $employees->perPage()]) }}" class="flex items-center text-sm text-gray-600 hover:text-gray-900">Clear</a>
                @endif
            </div>
        </div>

        <div>
            <label for="employee-per-page" class="block text-sm font-medium text-gray-700 mb-1">Records per page</label>
            <select id="employee-per-page" name="per_page" onchange="this.form.submit()"
                class="rounded border-gray-300 border px-3 py-2 focus:border-blue-500 focus:ring-blue-500">
                @foreach([10, 25, 50, 100] as $size)
                    <option value="{{ $size }}" @selected($employees->perPage() === $size)>{{ $size }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <div class="bg-white shadow-md rounded-b overflow-x-auto">
        <table class="min-w-full w-full table-auto">
            <thead>
                <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-left">Name / Email</th>
                    <th class="py-3 px-6 text-left">NoahFace ID</th>
                    <th class="py-3 px-6 text-left">Award</th>
                    <th class="py-3 px-6 text-left">Companies</th>
                    <th class="py-3 px-6 text-left">Type</th>
                    <th class="py-3 px-6 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm font-light">
                @forelse($employees as $employee)
                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                        <td class="py-3 px-6 text-left">
                            <div class="flex flex-wrap gap-1">@forelse($employee->companies as $company)<span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">{{ $company->name }}</span>@empty<span class="text-gray-400">None</span>@endforelse</div>
                        </td>
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
                        <td colspan="6" class="text-center py-4">No employees found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $employees->links() }}
    </div>
</div>
@endsection
