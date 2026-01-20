<?php

namespace App\Http\Controllers\Pic\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

        $email = $request->input('email');
        $password = $request->input('password');
        $remember = $request->filled('remember');

        // Find PIC by email
        $pic = Pic::where('email', $email)->first();
        
        if (!$pic) {
            return back()->withErrors([
                'email' => 'PIC dengan email ini tidak ditemukan.',
            ])->withInput($request->only('email'));
        }

        // Check if PIC is active
        if (!$pic->is_active) {
            return back()->withErrors([
                'email' => 'Akun Anda tidak aktif. Hubungi administrator.',
            ])->withInput($request->only('email'));
        }

        // Check password
        if (!Hash::check($password, $pic->password)) {
            return back()->withErrors([
                'email' => 'Password salah.',
            ])->withInput($request->only('email'));
        }

        // Login the PIC
        Auth::guard('pic')->login($pic, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('pic.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::guard('pic')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('pic.login');
    }
}
