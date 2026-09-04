<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::toMailUsing(function (object $notifiable, string $token): MailMessage {
            $resetUrl = route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);

            return (new MailMessage)
                ->subject('Reset your NoahFace Sync password')
                ->view('emails.auth.reset-password', [
                    'name' => $notifiable->name,
                    'resetUrl' => $resetUrl,
                    'expiresIn' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire'),
                ])
                ->text('emails.auth.reset-password-text', [
                    'name' => $notifiable->name,
                    'resetUrl' => $resetUrl,
                    'expiresIn' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire'),
                ]);
        });
    }
}
