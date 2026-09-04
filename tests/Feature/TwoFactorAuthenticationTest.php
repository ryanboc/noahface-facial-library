<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_enabled_user_must_complete_two_factor_challenge_before_login(): void
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $user = User::factory()->create([
            'password' => Hash::make('correct-password'),
            'google2fa_secret' => $secret,
        ]);

        $this->post(route('login.submit'), [
            'email' => $user->email,
            'password' => 'correct-password',
        ])->assertRedirect(route('2fa.challenge'));

        $this->assertGuest();

        $this->post(route('2fa.verify'), [
            'pin' => $google2fa->getCurrentOtp($secret),
        ])->assertRedirect(route('profile.show'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_two_factor_code_does_not_log_user_in(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('correct-password'),
            'google2fa_secret' => (new Google2FA())->generateSecretKey(),
        ]);

        $this->post(route('login.submit'), [
            'email' => $user->email,
            'password' => 'correct-password',
        ]);

        $this->post(route('2fa.verify'), ['pin' => '000000'])
            ->assertSessionHasErrors('pin');

        $this->assertGuest();
    }

    public function test_challenge_without_pending_login_returns_to_login(): void
    {
        $this->get(route('2fa.challenge'))->assertRedirect(route('login'));
        $this->post(route('2fa.verify'), ['pin' => '123456'])->assertRedirect(route('login'));
    }

    public function test_password_reset_can_explicitly_disable_two_factor_authentication(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
            'google2fa_secret' => (new Google2FA())->generateSecretKey(),
        ]);
        $token = \Illuminate\Support\Facades\Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
            'reset_two_factor' => '1',
        ])->assertRedirect(route('login'));

        $this->assertNull($user->fresh()->google2fa_secret);
    }

    public function test_password_reset_keeps_two_factor_enabled_by_default(): void
    {
        $secret = (new Google2FA())->generateSecretKey();
        $user = User::factory()->create(['google2fa_secret' => $secret]);
        $token = \Illuminate\Support\Facades\Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])->assertRedirect(route('login'));

        $this->assertSame($secret, $user->fresh()->google2fa_secret);
    }
}
