<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TwilioService
{
    public function send(string $recipient, string $body): array
    {
        $accountSid = (string) config('services.twilio.account_sid');
        $authToken = (string) config('services.twilio.auth_token');
        $from = (string) config('services.twilio.from');

        if ($accountSid === '' || $authToken === '' || $from === '') {
            throw new RuntimeException('Twilio is not configured. Add TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN, and TWILIO_FROM_NUMBER to the environment.');
        }

        $response = $this->client($accountSid, $authToken)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                'To' => $recipient,
                'From' => $from,
                'Body' => $body,
            ]);

        if ($response->failed()) {
            throw new RuntimeException((string) ($response->json('message') ?: 'Twilio rejected the message.'));
        }

        return [
            'sid' => (string) $response->json('sid'),
            'status' => (string) ($response->json('status') ?: 'queued'),
        ];
    }

    protected function client(string $accountSid, string $authToken): PendingRequest
    {
        // Do not automatically retry SMS requests: a timed-out request may still
        // have been accepted by Twilio, and retrying could send a duplicate.
        return Http::withBasicAuth($accountSid, $authToken)->timeout(15);
    }
}
