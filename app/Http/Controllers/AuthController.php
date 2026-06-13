<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    // -------------------------------------------------------
    // Show login form
    // GET /login
    // -------------------------------------------------------
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('tasks.index');
        }
        return view('auth.login');
    }

    // -------------------------------------------------------
    // Handle login form submission
    // POST /login
    // -------------------------------------------------------
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('tasks.index'));
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'These credentials do not match our records.']);
    }

    // -------------------------------------------------------
    // Show register form
    // GET /register
    // -------------------------------------------------------
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('tasks.index');
        }
        return view('auth.register');
    }

    // -------------------------------------------------------
    // Handle register form submission
    // POST /register
    // -------------------------------------------------------
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        return redirect()->route('tasks.index');
    }

    // -------------------------------------------------------
    // Handle logout
    // POST /logout
 
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}