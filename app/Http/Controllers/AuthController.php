<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

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
        // 1. Validate the user input
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // 2. Attempt to authenticate against the users table
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Prevent session fixation attacks
            $request->session()->regenerate();
 
            // Redirect to your main page (or wherever they were trying to go)
            return redirect()->intended('/employees');
        }

        // 3. Return back if authentication fails
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
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