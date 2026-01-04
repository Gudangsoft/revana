<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        $settings = [
            'app_name' => env('APP_NAME', 'REVANA'),
            'tagline' => Setting::get('tagline', 'Review Validation & Analytics'),
            'address' => Setting::get('address', ''),
            'contact' => Setting::get('contact', ''),
            'logo' => Setting::get('logo', ''),
            'favicon' => Setting::get('favicon', ''),
        ];
        
        return view('auth.login', compact('settings'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            
            // Redirect based on user role
            if ($user->role === 'admin') {
                return redirect()->intended('/admin/dashboard');
            } elseif ($user->role === 'reviewer') {
                return redirect()->intended('/reviewer/dashboard');
            }
            
            // Default redirect based on role
            if ($user->role === 'admin') {
                return redirect()->intended('/admin/dashboard');
            } elseif ($user->role === 'reviewer') {
                return redirect()->intended('/reviewer/dashboard');
            }
            return redirect()->intended('/login');
        }

        // Log failed login attempt
        \Log::warning('Failed login attempt', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
