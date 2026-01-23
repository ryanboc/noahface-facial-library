@props(['employee' => null, 'awards'])

@php
    $isEdit = !is_null($employee);
    $route = $isEdit ? route('employees.update', $employee) : route('employees.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<form action="{{ $route }}" method="POST" class="bg-white p-6 rounded shadow-md">
    @csrf
    @if($isEdit) @method($method) @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        {{-- 1. Personal Details --}}
        <div>
            <label class="block text-gray-700 font-bold mb-2">Full Name</label>
            <input type="text" name="name" value="{{ old('name', $employee->name ?? '') }}" 
                   class="w-full border p-2 rounded" required>
            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-gray-700 font-bold mb-2">Email Address</label>
            <input type="email" name="email" value="{{ old('email', $employee->email ?? '') }}" 
                   class="w-full border p-2 rounded" required>
            @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        {{-- 2. NoahFace Link --}}
        <div class="col-span-1 md:col-span-2">
            <label class="block text-gray-700 font-bold mb-2">NoahFace ID (User ID / Badge No)</label>
            <input type="text" name="noahface_id" value="{{ old('noahface_id', $employee->noahface_id ?? '') }}" 
                   class="w-full border p-2 rounded bg-yellow-50" placeholder="e.g. NF-10234" required>
            <p class="text-xs text-gray-500 mt-1">Must match the ID sent in the webhook payload.</p>
            @error('noahface_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        {{-- 3. Award Configuration --}}
        <div>
            <label class="block text-gray-700 font-bold mb-2">Select Award</label>
            <select name="award_id" class="w-full border p-2 rounded" required>
                <option value="">-- Choose Award --</option>
                @foreach($awards as $award)
                    <option value="{{ $award->id }}" 
                        {{ old('award_id', $employee->award_id ?? '') == $award->id ? 'selected' : '' }}>
                        {{ $award->name }}
                    </option>
                @endforeach
            </select>
            @error('award_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-gray-700 font-bold mb-2">Employment Type</label>
            <select name="employment_type" class="w-full border p-2 rounded" required>
                <option value="">-- Choose Type --</option>
                [cite_start]{{-- Based on columns found in document [cite: 3] --}}
                <option value="Casual" {{ old('employment_type', $employee->employment_type ?? '') == 'Casual' ? 'selected' : '' }}>
                    Casual
                </option>
                <option value="Full Time/Part Time" {{ old('employment_type', $employee->employment_type ?? '') == 'Full Time/Part Time' ? 'selected' : '' }}>
                    Full Time / Part Time
                </option>
            </select>
            @error('employment_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="mt-6 flex justify-end">
        <a href="{{ route('employees.index') }}" class="mr-4 px-6 py-2 text-gray-600 border rounded hover:bg-gray-100">Cancel</a>
        <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded hover:bg-green-700">
            {{ $isEdit ? 'Update Link' : 'Link Employee' }}
        </button>
    </div>
</form>