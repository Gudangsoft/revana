<?php

namespace App\Http\Controllers\Pic\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('pic.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        // Debug: Check if PIC exists
        $pic = \App\Models\Pic::where('email', $credentials['email'])->first();
        if (!$pic) {
            return back()->withErrors([
                'email' => 'PIC dengan email ini tidak ditemukan.',
            ])->withInput($request->only('email'));
        }

        // Debug: Check password
        if (!\Illuminate\Support\Facades\Hash::check($credentials['password'], $pic->password)) {
            return back()->withErrors([
                'email' => 'Password salah.',
            ])->withInput($request->only('email'));
        }

        // Try to login
        if (Auth::guard('pic')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            $pic = Auth::guard('pic')->user();
            
            if (!$pic->is_active) {
                Auth::guard('pic')->logout();
                return back()->withErrors([
                    'email' => 'Akun Anda tidak aktif.',
                ]);
            }

            return redirect()->intended(route('pic.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Login gagal. Silakan coba lagi.',
        ])->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        Auth::guard('pic')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('pic.login');
    }
}
