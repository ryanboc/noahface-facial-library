@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6"><div><p class="text-sm font-semibold uppercase tracking-wide text-blue-600">Organisation</p><h1 class="text-3xl font-bold">Companies & Workplaces</h1></div><a href="{{ route('companies.create') }}" class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700">+ Add company</a></div>
    <div class="overflow-x-auto rounded-xl border bg-white shadow-sm"><table class="min-w-full"><thead class="bg-gray-100 text-left text-sm uppercase text-gray-600"><tr><th class="px-5 py-3">Company</th><th class="px-5 py-3">Address</th><th class="px-5 py-3 text-center">Employees</th><th class="px-5 py-3 text-right">Actions</th></tr></thead><tbody class="divide-y">
        @forelse($companies as $company)<tr><td class="px-5 py-4"><p class="font-semibold">{{ $company->name }}</p>@if($company->notes)<p class="mt-1 text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($company->notes, 80) }}</p>@endif</td><td class="px-5 py-4 text-sm text-gray-600">{{ $company->address ?: '—' }}</td><td class="px-5 py-4 text-center"><span class="rounded-full bg-blue-100 px-2.5 py-1 text-sm font-semibold text-blue-700">{{ $company->employees_count }}</span></td><td class="px-5 py-4"><div class="flex justify-end gap-3"><a href="{{ route('companies.edit', $company) }}" class="font-semibold text-blue-600">Edit</a><form method="POST" action="{{ route('companies.destroy', $company) }}" onsubmit="return confirm('Delete this company? Employee records will not be deleted.');">@csrf @method('DELETE')<button class="font-semibold text-red-600">Delete</button></form></div></td></tr>
        @empty<tr><td colspan="4" class="p-10 text-center text-gray-500">No companies found.</td></tr>@endforelse
    </tbody></table></div><div class="mt-4">{{ $companies->links() }}</div>
</div>
@endsection
