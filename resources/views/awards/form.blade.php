@props(['award' => null])

@php
    // Determine if we are editing or creating to set the form action
    $isEdit = !is_null($award);
    $route = $isEdit ? route('awards.update', $award) : route('awards.store');
    $method = $isEdit ? 'PUT' : 'POST';
    
    // Prepare the initial rates data:
    // 1. If validation failed, use old('rates')
    // 2. If editing, use existing database rates
    // 3. Otherwise, default to one empty row
    $rates = old('rates', $isEdit ? $award->rates : [ ['employment_type' => '', 'category' => '', 'rate_value' => ''] ]);
@endphp

<form action="{{ $route }}" method="POST" id="awardForm">
    @csrf
    @if($isEdit) @method($method) @endif

    {{-- SECTION 1: Basic Award Details --}}
    <div class="mb-6 p-4 border rounded bg-white">
        <h3 class="text-lg font-bold mb-4">Award Details</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-medium">Award Name</label>
                <input type="text" name="name" class="w-full border p-2 rounded" 
                       value="{{ old('name', $award->name ?? '') }}" required>
                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block font-medium">Pay Guide Link (URL)</label>
                <input type="url" name="pay_guide_link" class="w-full border p-2 rounded" 
                       value="{{ old('pay_guide_link', $award->pay_guide_link ?? '') }}">
            </div>
        </div>
    </div>

    {{-- SECTION 2: Conditions (Text Rules) --}}
    <div class="mb-6 p-4 border rounded bg-white">
        <h3 class="text-lg font-bold mb-4">Conditions & Allowances</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            
            {{-- Loop through fields to keep code clean --}}
            @foreach(['hours_per_day_rule', 'leading_hand_allowance', 'meal_allowance', 'paid_break_rule', 'unpaid_break_rule', 'remarks'] as $field)
                <div>
                    <label class="block font-medium capitalize">{{ str_replace('_', ' ', $field) }}</label>
                    <textarea name="conditions[{{ $field }}]" rows="2" class="w-full border p-2 rounded">{{ old("conditions.$field", $award->conditions->$field ?? '') }}</textarea>
                </div>
            @endforeach

        </div>
    </div>

    {{-- SECTION 3: Dynamic Rates Table --}}
    <div class="mb-6 p-4 border rounded bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold">Penalty Rates</h3>
            <button type="button" onclick="addRateRow()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                + Add Rate
            </button>
        </div>

        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-100 border-b">
                    <th class="p-2">Employment Type</th>
                    <th class="p-2">Category (e.g., Overtime)</th>
                    <th class="p-2">Rate (e.g., 150%)</th>
                    <th class="p-2">Action</th>
                </tr>
            </thead>
            <tbody id="rates-container">
                {{-- Loop through existing rates (from DB or Old Input) --}}
                @foreach($rates as $index => $rate)
                    <tr class="rate-row border-b">
                        <td class="p-2">
                            <select name="rates[{{ $index }}][employment_type]" class="w-full border p-2 rounded" required>
                                <option value="">Select Type</option>
                                <option value="Casual" {{ (is_array($rate) ? $rate['employment_type'] : $rate->employment_type) == 'Casual' ? 'selected' : '' }}>Casual</option>
                                <option value="Full Time/Part Time" {{ (is_array($rate) ? $rate['employment_type'] : $rate->employment_type) == 'Full Time/Part Time' ? 'selected' : '' }}>Full Time/Part Time</option>
                            </select>
                            @error("rates.$index.employment_type") <div class="text-red-500 text-xs">{{ $message }}</div> @enderror
                        </td>
                        <td class="p-2">
                            <input type="text" name="rates[{{ $index }}][category]" 
                                   class="w-full border p-2 rounded" 
                                   value="{{ is_array($rate) ? $rate['category'] : $rate->category }}" required>
                             @error("rates.$index.category") <div class="text-red-500 text-xs">{{ $message }}</div> @enderror
                        </td>
                        <td class="p-2">
                            <input type="text" name="rates[{{ $index }}][rate_value]" 
                                   class="w-full border p-2 rounded" 
                                   value="{{ is_array($rate) ? $rate['rate_value'] : $rate->rate_value }}" required>
                            @error("rates.$index.rate_value") <div class="text-red-500 text-xs">{{ $message }}</div> @enderror
                        </td>
                        <td class="p-2">
                            <button type="button" onclick="removeRow(this)" class="text-red-600 hover:text-red-800 font-bold">X</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="flex justify-end">
        <a href="{{ route('awards.index') }}" class="mr-4 px-6 py-2 text-gray-600 border rounded hover:bg-gray-100">Cancel</a>
        <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded hover:bg-green-700">
            {{ $isEdit ? 'Update Award' : 'Save Award' }}
        </button>
    </div>
</form>

{{-- JAVASCRIPT FOR DYNAMIC ROWS --}}
<script>
    // We use a counter to ensure unique indices for new rows
    // Start counting from the current number of rows
    let rateIndex = {{ count($rates) }};

    function addRateRow() {
        const container = document.getElementById('rates-container');
        const newRow = `
            <tr class="rate-row border-b">
                <td class="p-2">
                    <select name="rates[${rateIndex}][employment_type]" class="w-full border p-2 rounded" required>
                        <option value="">Select Type</option>
                        <option value="Casual">Casual</option>
                        <option value="Full Time/Part Time">Full Time/Part Time</option>
                    </select>
                </td>
                <td class="p-2">
                    <input type="text" name="rates[${rateIndex}][category]" class="w-full border p-2 rounded" placeholder="e.g. Public Holiday" required>
                </td>
                <td class="p-2">
                    <input type="text" name="rates[${rateIndex}][rate_value]" class="w-full border p-2 rounded" placeholder="e.g. 250%" required>
                </td>
                <td class="p-2">
                    <button type="button" onclick="removeRow(this)" class="text-red-600 hover:text-red-800 font-bold">X</button>
                </td>
            </tr>
        `;
        
        container.insertAdjacentHTML('beforeend', newRow);
        rateIndex++;
    }

    function removeRow(button) {
        // Optional: Prevent removing the last row if you always want at least one
        const row = button.closest('tr');
        row.remove();
    }
</script>