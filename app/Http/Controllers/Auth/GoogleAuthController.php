<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Marketing;
use App\Models\Pic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(string $portal)
    {
        if (!config('services.google.client_id')) {
            return redirect()->route($portal . '.login')
                ->with('error', 'Login Google belum dikonfigurasi oleh admin.');
        }

        session(['google_portal' => $portal]);

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request)
    {
        $portal = session('google_portal', 'pic');

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()->route($portal . '.login')
                ->with('error', 'Login Google gagal. Silakan coba lagi.');
        }

        $email = $googleUser->getEmail();

        if ($portal === 'pic') {
            $user = Pic::where('email', $email)->where('is_active', true)->first();

            if (!$user) {
                return redirect()->route('pic.login')
                    ->with('error', 'Email ' . $email . ' tidak terdaftar sebagai PIC aktif.');
            }

            Auth::guard('pic')->login($user);
            $request->session()->regenerate();
            return redirect()->route('pic.dashboard');
        }

        if ($portal === 'marketing') {
            $user = Marketing::where('email', $email)->first();

            if (!$user) {
                return redirect()->route('marketing.login')
                    ->with('error', 'Email ' . $email . ' tidak terdaftar sebagai Marketing.');
            }

            Auth::guard('marketing')->login($user);
            $request->session()->regenerate();
            return redirect()->route('marketing.dashboard');
        }

        return redirect('/');
    }
}