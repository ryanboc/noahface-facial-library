<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    // Show the login form
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    // Handle the login attempt
    public function login(Request $request): RedirectResponse
    {
        // 1. Validate the input
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // 2. Find the user
        $user = User::where('email', $request->email)->first();

        // 3. Check if user exists and password is correct
        if ($user && Hash::check($request->password, $user->password)) {
            
            // 4. Does this user have 2FA enabled?
            if ($user->google2fa_secret) {
                // HOLDING PATTERN: Put their ID in the session, but DO NOT log them in yet.
                $request->session()->put('2fa:user:id', $user->id);
                $request->session()->put('2fa:auth:remember', $request->boolean('remember'));
                
                // Send them to the 6-digit pin screen
                return redirect()->route('2fa.challenge');
            }

            // 5. No 2FA? Log them in normally.
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            return redirect()->intended('/employees');
        }

        // 6. Password failed
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }
    // ---------------------------------------------------------
    // REGISTRATION
    // ---------------------------------------------------------

    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        // 1. Validate the new user data
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // 2. Create the user in the database
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // 3. Instantly log them in after registering
        Auth::login($user);
        $request->session()->regenerate();

        // 4. Send them to the dashboard
        return redirect('/employees')->with('success', 'Account created successfully! Please click your name in the top right to setup 2FA.');
    }

    // Handle logout
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login');
    }
}