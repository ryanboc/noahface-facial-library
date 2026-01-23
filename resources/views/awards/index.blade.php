@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    
    {{-- Header Section --}}
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Awards List</h1>
        <a href="{{ route('awards.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            + Create New Award
        </a>
    </div>

    {{-- Awards Table --}}
    <div class="bg-white shadow-md rounded my-6 overflow-x-auto">
        <table class="min-w-full w-full table-auto">
            <thead>
                <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-left">ID</th>
                    <th class="py-3 px-6 text-left">Award Name</th>
                    <th class="py-3 px-6 text-center">Rates Count</th>
                    <th class="py-3 px-6 text-center">Pay Guide</th>
                    <th class="py-3 px-6 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm font-light">
                @forelse($awards as $award)
                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                        <td class="py-3 px-6 text-left whitespace-nowrap font-bold">
                            {{ $award->id }}
                        </td>
                        <td class="py-3 px-6 text-left">
                            <span class="font-medium">{{ $award->name }}</span>
                        </td>
                        <td class="py-3 px-6 text-center">
                            {{-- Count how many rates are attached to this award --}}
                            <span class="bg-blue-200 text-blue-600 py-1 px-3 rounded-full text-xs">
                                {{ $award->rates->count() }}
                            </span>
                        </td>
                        <td class="py-3 px-6 text-center">
                            @if($award->pay_guide_link)
                                <a href="{{ $award->pay_guide_link }}" target="_blank" class="text-blue-500 hover:underline">
                                    View PDF
                                </a>
                            @else
                                <span class="text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="py-3 px-6 text-center">
                            <div class="flex item-center justify-center space-x-2">
                                {{-- Edit Button --}}
                                <a href="{{ route('awards.edit', $award) }}" class="w-4 mr-2 transform hover:text-purple-500 hover:scale-110">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </a>

                                {{-- Delete Button (Requires a Form) --}}
                                <form action="{{ route('awards.destroy', $award) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this award?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-4 transform hover:text-red-500 hover:scale-110">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-4 px-6 text-center text-gray-500">
                            No awards found. Click "Create New Award" to add one.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination Links --}}
    <div class="mt-4">
        {{ $awards->links() }} 
    </div>
</div>
@endsection