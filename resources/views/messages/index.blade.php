@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6 flex items-center justify-between">
        <div><p class="text-sm font-semibold uppercase tracking-wide text-blue-600">Twilio SMS</p><h1 class="text-3xl font-bold">Messages</h1><p class="mt-1 text-sm text-gray-500">Save reusable messages, customise them before sending, and review recent deliveries.</p></div>
        <a href="{{ route('messages.create') }}" class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700">+ New template</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        @forelse($templates as $template)
            <article class="rounded-xl border bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4"><div><h2 class="text-lg font-bold">{{ $template->name }}</h2><p class="text-xs text-gray-400">Updated {{ $template->updated_at->diffForHumans() }}</p></div><div class="flex gap-3"><a href="{{ route('messages.edit', $template) }}" class="text-sm font-semibold text-blue-600">Edit</a><form method="POST" action="{{ route('messages.destroy', $template) }}" onsubmit="return confirm('Delete this template? Delivery history will be retained.');">@csrf @method('DELETE')<button class="text-sm font-semibold text-red-600">Delete</button></form></div></div>
                <p class="my-4 whitespace-pre-wrap rounded-lg bg-gray-50 p-3 text-sm text-gray-700">{{ $template->body }}</p>
                <form method="POST" action="{{ route('messages.send', $template) }}" class="space-y-3 border-t pt-4">
                    @csrf
                    <div><label class="mb-1 block text-sm font-semibold">Recipient</label><input name="recipient" value="{{ old('recipient') }}" required inputmode="tel" placeholder="+61412345678" class="w-full rounded-lg border px-3 py-2"></div>
                    <div><label class="mb-1 block text-sm font-semibold">Message <span class="font-normal text-gray-400">(editable for this send)</span></label><textarea name="body" required maxlength="1600" rows="4" class="w-full rounded-lg border px-3 py-2">{{ old('body', $template->body) }}</textarea></div>
                    <div class="flex justify-end"><button class="rounded-lg bg-green-600 px-4 py-2 font-semibold text-white hover:bg-green-700">Send text</button></div>
                </form>
            </article>
        @empty
            <div class="rounded-xl border bg-white p-10 text-center text-gray-500 lg:col-span-2">No templates yet. Create one to send your first message.</div>
        @endforelse
    </div>
    <div class="mt-4">{{ $templates->links() }}</div>

    <h2 class="mb-3 mt-10 text-xl font-bold">Recent delivery history</h2>
    <div class="overflow-x-auto rounded-xl border bg-white shadow-sm"><table class="min-w-full"><thead class="bg-gray-100 text-left text-sm uppercase text-gray-600"><tr><th class="px-5 py-3">Recipient</th><th class="px-5 py-3">Template</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Sent</th></tr></thead><tbody class="divide-y">
        @forelse($recentMessages as $sent)<tr><td class="px-5 py-4 font-medium">{{ $sent->recipient }}</td><td class="px-5 py-4 text-sm text-gray-600">{{ $sent->template?->name ?? 'Deleted template' }}</td><td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $sent->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}" title="{{ $sent->error_message }}">{{ ucfirst($sent->status) }}</span></td><td class="px-5 py-4 text-sm text-gray-500">{{ ($sent->sent_at ?? $sent->created_at)->diffForHumans() }}</td></tr>
        @empty<tr><td colspan="4" class="p-8 text-center text-gray-500">No messages sent yet.</td></tr>@endforelse
    </tbody></table></div>
</div>
@endsection
