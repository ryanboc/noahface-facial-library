@props(['company' => null])

<form method="POST" action="{{ $company ? route('companies.update', $company) : route('companies.store') }}" class="max-w-2xl rounded-xl border bg-white p-6 shadow-sm space-y-5">
    @csrf
    @if($company) @method('PUT') @endif
    <div><label class="block text-sm font-semibold mb-1">Company name</label><input name="name" value="{{ old('name', $company?->name) }}" required class="w-full rounded-lg border px-3 py-2.5">@error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
    <div><label class="block text-sm font-semibold mb-1">Address <span class="font-normal text-gray-400">(optional)</span></label><input name="address" value="{{ old('address', $company?->address) }}" class="w-full rounded-lg border px-3 py-2.5">@error('address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
    <div><label class="block text-sm font-semibold mb-1">Notes <span class="font-normal text-gray-400">(optional)</span></label><textarea name="notes" rows="4" class="w-full rounded-lg border px-3 py-2.5">{{ old('notes', $company?->notes) }}</textarea>@error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
    <div class="flex justify-end gap-3"><a href="{{ route('companies.index') }}" class="rounded-lg border px-4 py-2 text-gray-600">Cancel</a><button class="rounded-lg bg-blue-600 px-5 py-2 font-semibold text-white hover:bg-blue-700">{{ $company ? 'Update company' : 'Create company' }}</button></div>
</form>
