<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function login()
    {
        if (auth()->check()) {
            return redirect()->route('admin.map');
        }

        return view('admin.login');
    }

    /**
     * Authenticate the admin login request.
     */
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.map'));
    }

    /**
     * Log the admin out and redirect home.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    /**
     * Show the admin TODA map interface.
     */
    public function map()
    {
        return view('admin.map');
    }
}