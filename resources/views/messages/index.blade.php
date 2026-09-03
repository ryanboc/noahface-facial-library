@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6 flex items-center justify-between">
        <div><p class="text-sm font-semibold uppercase tracking-wide text-blue-600">Twilio SMS</p><h1 class="text-3xl font-bold">Message templates</h1><p class="mt-1 text-sm text-gray-500">Create the reusable message here, then send it to everyone from the weekly roster.</p></div>
        <div class="flex gap-2"><a href="{{ route('roster.index') }}" class="rounded-lg border border-green-600 px-4 py-2 font-semibold text-green-700 hover:bg-green-50">Go to weekly roster</a><a href="{{ route('messages.create') }}" class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700">+ New template</a></div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        @forelse($templates as $template)
            <article class="rounded-xl border bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4"><div><h2 class="text-lg font-bold">{{ $template->name }}</h2><p class="text-xs text-gray-400">Updated {{ $template->updated_at->diffForHumans() }}</p></div><div class="flex gap-3"><a href="{{ route('messages.edit', $template) }}" class="text-sm font-semibold text-blue-600">Edit</a><form method="POST" action="{{ route('messages.destroy', $template) }}" onsubmit="return confirm('Delete this template? Delivery history will be retained.');">@csrf @method('DELETE')<button class="text-sm font-semibold text-red-600">Delete</button></form></div></div>
                <p class="mt-4 whitespace-pre-wrap rounded-lg bg-gray-50 p-3 text-sm text-gray-700">{{ $template->body }}</p>
            </article>
        @empty
            <div class="rounded-xl border bg-white p-10 text-center text-gray-500 lg:col-span-2">No templates yet. Create one to send your first message.</div>
        @endforelse
    </div>
    <div class="mt-4">{{ $templates->links() }}</div>

    <h2 class="mb-3 mt-10 text-xl font-bold">Recent delivery history</h2>
    <div class="overflow-x-auto rounded-xl border bg-white shadow-sm"><table class="min-w-full"><thead class="bg-gray-100 text-left text-sm uppercase text-gray-600"><tr><th class="px-5 py-3">Recipient</th><th class="px-5 py-3">Template</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Twilio response</th><th class="px-5 py-3">Sent</th></tr></thead><tbody class="divide-y">
        @forelse($recentMessages as $sent)<tr><td class="px-5 py-4 font-medium">{{ $sent->recipient }}</td><td class="px-5 py-4 text-sm text-gray-600">{{ $sent->template?->name ?? 'Standard roster message' }}</td><td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $sent->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">{{ ucfirst($sent->status) }}</span></td><td class="max-w-md px-5 py-4 text-sm {{ $sent->error_message ? 'text-red-700' : 'text-gray-500' }}">{{ $sent->error_message ?: ($sent->twilio_sid ? 'Twilio SID: '.$sent->twilio_sid : '—') }}</td><td class="px-5 py-4 text-sm text-gray-500">{{ ($sent->sent_at ?? $sent->created_at)->diffForHumans() }}</td></tr>
        @empty<tr><td colspan="5" class="p-8 text-center text-gray-500">No messages sent yet.</td></tr>@endforelse
    </tbody></table></div>
</div>
@endsection
