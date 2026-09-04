<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA; // The math engine
use BaconQrCode\Renderer\ImageRenderer; // The QR SVG generator
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorController extends Controller
{
    public function showSetup(Request $request): View|RedirectResponse
    {
        if ($request->user()->google2fa_secret) {
            return redirect()->route('profile.show')->with('status', 'Two-factor authentication is already enabled.');
        }

        $google2fa = new Google2FA();

        $secret = $google2fa->generateSecretKey();

        //Store it in the session temporarily (Do NOT save to DB yet!)
        $request->session()->put('2fa_setup_secret', $secret);

        // Generate the special string (URI) that Google Authenticator reads
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),      // The name that shows up in their app
            Auth::user()->email,     // The email that shows up in their app
            $secret                  // The unique key
        );

        // Use BaconQrCode to convert that string into a physical SVG image
        $renderer = new ImageRenderer(
            new RendererStyle(256), // Size of the QR Code (256x256 pixels)
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($qrCodeUrl);

        // Pass the physical SVG image and the raw text secret to the Blade view
        return view('auth.2fa-setup', [
            'qrCodeSvg' => $qrCodeSvg,
            'secret' => $secret
        ]);
    }

    // Process the setup (Verify their pin and save to DB)
    public function enable(Request $request): RedirectResponse
    {
        if ($request->user()->google2fa_secret) {
            $request->session()->forget('2fa_setup_secret');

            return redirect()->route('profile.show')->with('status', 'Two-factor authentication is already enabled.');
        }

        // Validate they actually typed a pin
        $request->validate([
            'pin' => ['required', 'digits:6']
        ]);

        $user = Auth::user();
        
        // Retrieve the secret we temporarily stored in the session
        $secret = $request->session()->get('2fa_setup_secret');

        if (! is_string($secret) || $secret === '') {
            return redirect()->route('2fa.setup')->withErrors([
                'pin' => 'Your setup session expired. Please scan the new QR code and try again.',
            ]);
        }
        
        $google2fa = new Google2FA();

        // Check if the 6-digit pin they typed matches the secret key
        $isValid = $google2fa->verifyKey($secret, $request->pin);

        if ($isValid) {
            // SUCCESS! They scanned it correctly. Now lock it into the database.
            $user->google2fa_secret = $secret;
            $user->save();

            // Clean up the session
            $request->session()->forget('2fa_setup_secret');

            // Send them to the dashboard with a success message
            return redirect()->route('profile.show')->with('success', 'Two-Factor Authentication is now enabled!');
        }

        // FAILED. They typed the wrong pin. Send them back to try again.
        return back()->withErrors(['pin' => 'Invalid code. Please try again.']);
    }

    public function showChallenge(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('2fa:user:id')) {
            return redirect()->route('login')->withErrors([
                'email' => 'Your sign-in session expired. Please sign in again.',
            ]);
        }

        return view('auth.2fa-challenge');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'pin' => ['required', 'digits:6'],
        ]);

        $userId = $request->session()->get('2fa:user:id');
        $user = $userId ? User::find($userId) : null;

        if (! $user || ! $user->google2fa_secret) {
            $this->clearPendingLogin($request);

            return redirect()->route('login')->withErrors([
                'email' => 'Your sign-in session expired. Please sign in again.',
            ]);
        }

        $throttleKey = '2fa:'.sha1($user->id.'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return back()->withErrors([
                'pin' => 'Too many attempts. Try again in '.RateLimiter::availableIn($throttleKey).' seconds.',
            ]);
        }

        $isValid = (new Google2FA())->verifyKey($user->google2fa_secret, $request->string('pin')->toString());

        if (! $isValid) {
            RateLimiter::hit($throttleKey, 60);

            return back()->withErrors(['pin' => 'Invalid authentication code. Please try again.']);
        }

        RateLimiter::clear($throttleKey);
        $remember = $request->session()->get('2fa:auth:remember', false);
        $this->clearPendingLogin($request);
        Auth::login($user, (bool) $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('profile.show'));
    }

    private function clearPendingLogin(Request $request): void
    {
        $request->session()->forget(['2fa:user:id', '2fa:auth:remember']);
    }
}
