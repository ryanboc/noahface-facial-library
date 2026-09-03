<?php

namespace Tests\Feature;

use App\Models\MessageTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MessageTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_manage_message_templates(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);

        $this->actingAs($manager)->post(route('messages.store'), [
            'name' => 'Shift reminder', 'body' => 'Your shift starts at 8am.',
        ])->assertRedirect(route('messages.index'));

        $template = MessageTemplate::firstOrFail();
        $this->actingAs($manager)->put(route('messages.update', $template), [
            'name' => 'Updated reminder', 'body' => 'Your shift starts at 9am.',
        ])->assertRedirect(route('messages.index'));
        $this->assertDatabaseHas('message_templates', ['name' => 'Updated reminder']);

        $this->actingAs($manager)->delete(route('messages.destroy', $template))->assertRedirect(route('messages.index'));
        $this->assertDatabaseMissing('message_templates', ['id' => $template->id]);
    }

    public function test_manager_can_send_a_template_through_twilio(): void
    {
        config(['services.twilio.account_sid' => 'AC123', 'services.twilio.auth_token' => 'secret', 'services.twilio.from' => '+15551234567']);
        Http::fake(['api.twilio.com/*' => Http::response(['sid' => 'SM123', 'status' => 'queued'], 201)]);
        $manager = User::factory()->create(['role' => 'manager']);
        $template = MessageTemplate::create(['name' => 'Hello', 'body' => 'Original', 'created_by' => $manager->id]);

        $this->actingAs($manager)->post(route('messages.send', $template), [
            'recipient' => '+61412345678', 'body' => 'Customised message',
        ])->assertRedirect(route('messages.index'));

        $this->assertDatabaseHas('text_messages', [
            'recipient' => '+61412345678', 'body' => 'Customised message', 'twilio_sid' => 'SM123', 'status' => 'queued',
        ]);
        Http::assertSent(fn ($request) => $request['To'] === '+61412345678' && $request['Body'] === 'Customised message');
    }

    public function test_employee_cannot_access_messages(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $this->actingAs($employee)->get(route('messages.index'))->assertForbidden();
    }
}
