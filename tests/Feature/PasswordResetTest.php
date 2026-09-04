<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_a_password_reset_link(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_unknown_email_receives_the_same_safe_response(): void
    {
        Notification::fake();

        $this->post(route('password.email'), ['email' => 'unknown@example.com'])
            ->assertSessionHas('status', 'If an account exists for that email address, a password reset link has been sent.');

        Notification::assertNothingSent();
    }

    public function test_password_reset_email_uses_the_branded_design(): void
    {
        $user = User::factory()->create(['name' => 'Alex Taylor']);
        $notification = new ResetPassword('secure-test-token');

        $mail = $notification->toMail($user);

        $this->assertSame('Reset your NoahFace Sync password', $mail->subject);
        $this->assertSame([
            'html' => 'emails.auth.reset-password',
            'text' => 'emails.auth.reset-password-text',
        ], $mail->view);
        $this->assertStringContainsString('secure-test-token', $mail->viewData['resetUrl']);
        $this->assertSame('Alex Taylor', $mail->viewData['name']);
    }

    public function test_user_can_reset_their_password_with_a_valid_token(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password')]);
        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])->assertRedirect(route('login'))->assertSessionHas('status');

        $this->assertTrue(Hash::check('new-secure-password', $user->fresh()->password));
    }

    public function test_invalid_token_cannot_reset_a_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password')]);

        $this->from(route('password.reset', 'bad-token'))->post(route('password.update'), [
            'token' => 'bad-token',
            'email' => $user->email,
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])->assertRedirect(route('password.reset', 'bad-token'))->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }
}
