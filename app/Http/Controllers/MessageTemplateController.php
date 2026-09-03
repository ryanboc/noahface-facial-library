<?php

namespace App\Http\Controllers;

use App\Models\MessageTemplate;
use App\Models\TextMessage;
use App\Services\TwilioService;
use Illuminate\Http\Request;
use Throwable;

class MessageTemplateController extends Controller
{
    public function index()
    {
        $templates = MessageTemplate::with('creator')->latest()->paginate(15);
        $recentMessages = TextMessage::with('template')->latest()->limit(10)->get();

        return view('messages.index', compact('templates', 'recentMessages'));
    }

    public function create()
    {
        return view('messages.create');
    }

    public function store(Request $request)
    {
        MessageTemplate::create($this->validated($request) + ['created_by' => $request->user()->id]);

        return redirect()->route('messages.index')->with('success', 'Message template created.');
    }

    public function edit(MessageTemplate $message)
    {
        return view('messages.edit', compact('message'));
    }

    public function update(Request $request, MessageTemplate $message)
    {
        $message->update($this->validated($request));

        return redirect()->route('messages.index')->with('success', 'Message template updated.');
    }

    public function destroy(MessageTemplate $message)
    {
        $message->delete();

        return redirect()->route('messages.index')->with('success', 'Message template deleted.');
    }

    public function send(Request $request, MessageTemplate $message, TwilioService $twilio)
    {
        $data = $request->validate([
            'recipient' => ['required', 'regex:/^\+[1-9]\d{7,14}$/'],
            'body' => ['required', 'string', 'max:1600'],
        ], ['recipient.regex' => 'Enter a phone number in E.164 format, for example +61412345678.']);

        $textMessage = TextMessage::create([
            'message_template_id' => $message->id,
            'sent_by' => $request->user()->id,
            'recipient' => $data['recipient'],
            'body' => $data['body'],
        ]);

        try {
            $result = $twilio->send($data['recipient'], $data['body']);
            $textMessage->update([
                'twilio_sid' => $result['sid'],
                'status' => $result['status'],
                'sent_at' => now(),
            ]);

            return redirect()->route('messages.index')->with('success', 'Text message sent to Twilio.');
        } catch (Throwable $exception) {
            report($exception);
            $textMessage->update(['status' => 'failed', 'error_message' => $exception->getMessage()]);

            return back()->withInput()->withErrors(['twilio' => $exception->getMessage()]);
        }
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:1600'],
        ]);
    }
}
