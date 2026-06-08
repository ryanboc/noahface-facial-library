<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA; // The math engine
use BaconQrCode\Renderer\ImageRenderer; // The QR SVG generator
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorController extends Controller
{
    
    public function showSetup(Request $request)
    {
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
    public function enable(Request $request)
    {
        // Validate they actually typed a pin
        $request->validate([
            'pin' => ['required', 'numeric']
        ]);

        $user = Auth::user();
        
        // Retrieve the secret we temporarily stored in the session
        $secret = $request->session()->get('2fa_setup_secret');
        
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
            return redirect('/employees')->with('success', 'Two-Factor Authentication is now enabled!');
        }

        // FAILED. They typed the wrong pin. Send them back to try again.
        return back()->withErrors(['pin' => 'Invalid code. Please try again.']);
    }

    // ... (showChallenge and verify remain blank for now)
}