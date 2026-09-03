<form method="POST" action="{{ $message ? route('messages.update', $message) : route('messages.store') }}" class="max-w-2xl space-y-5 rounded-xl border bg-white p-6 shadow-sm">
    @csrf
    @if($message) @method('PUT') @endif
    <div>
        <label for="name" class="mb-1 block text-sm font-semibold">Template name</label>
        <input id="name" name="name" value="{{ old('name', $message?->name) }}" required maxlength="255" class="w-full rounded-lg border px-3 py-2.5" placeholder="Roster reminder">
        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <div class="mb-1 flex justify-between"><label for="body" class="block text-sm font-semibold">Message</label><span id="character-count" class="text-xs text-gray-500">0 / 1600</span></div>
        <textarea id="body" name="body" rows="7" required maxlength="1600" class="w-full rounded-lg border px-3 py-2.5" placeholder="Hi, this is a reminder that...">{{ old('body', $message?->body) }}</textarea>
        <p class="mt-1 text-xs text-gray-500">Long messages may be delivered by Twilio as multiple SMS segments.</p>
        @error('body')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="flex justify-end gap-3"><a href="{{ route('messages.index') }}" class="rounded-lg border px-4 py-2 text-gray-600">Cancel</a><button class="rounded-lg bg-blue-600 px-5 py-2 font-semibold text-white hover:bg-blue-700">{{ $message ? 'Update template' : 'Create template' }}</button></div>
</form>
<script>
    const body = document.getElementById('body');
    const count = document.getElementById('character-count');
    const updateCount = () => count.textContent = `${body.value.length} / 1600`;
    body.addEventListener('input', updateCount);
    updateCount();
</script>
