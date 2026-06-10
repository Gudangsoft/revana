<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\MotivationalMessage;
use App\Http\Controllers\Admin\SyncController;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

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

        $a = rand(1, 9);
        $b = rand(1, 9);
        session(['captcha_admin' => $a + $b]);
        $captcha_question = "$a + $b";

        return view('auth.login', compact('settings', 'captcha_question'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        // Verify math CAPTCHA (skip for force_login — password sudah cukup sebagai verifikasi)
        if (! $request->boolean('force_login')) {
            if ((int) $request->input('captcha_answer') !== (int) session('captcha_admin')) {
                return back()->withErrors(['email' => 'Jawaban verifikasi salah. Silakan coba lagi.'])->onlyInput('email');
            }
        }
        session()->forget('captcha_admin');

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Blokir login ganda untuk akun admin
            if ($user->role === 'admin') {
                $cacheKey = 'admin_session:' . $user->id;
                $activeSession = Cache::get($cacheKey);

                if ($activeSession && $activeSession !== session()->getId()) {
                    if (!$request->boolean('force_login')) {
                        Auth::logout();
                        $request->session()->invalidate();
                        $request->session()->regenerateToken();
                        return back()
                            ->withErrors([
                                'email' => 'Akun admin ini sedang aktif di sesi lain.',
                            ])
                            ->onlyInput('email')
                            ->with('session_conflict', true);
                    }
                    // Force login: hapus sesi lama, lanjutkan
                    Cache::forget($cacheKey);
                }

                Cache::put($cacheKey, session()->getId(), now()->addMinutes(config('session.lifetime', 120)));
            }

            // Flash sync notification for admin after login
            if ($user->role === 'admin') {
                try {
                    $outOfSync = SyncController::countOutOfSync();
                    if ($outOfSync > 0) {
                        $request->session()->flash(
                            'sync_warning',
                            "⚠️ Terdapat <strong>{$outOfSync} item data yang tidak sinkron</strong>. " .
                            "<a href=\"/admin/sync\" class=\"alert-link\">Klik di sini untuk memperbaiki →</a>"
                        );
                    } else {
                        $request->session()->flash('sync_info', '✅ Semua data tersinkronisasi dengan baik.');
                    }
                } catch (\Exception $e) {
                    // Silent — jangan ganggu proses login
                }

                ActivityLog::record('admin_login', $user, [], ['ip' => $request->ip()]);
                $request->session()->flash('motivational_message', MotivationalMessage::random());
                return redirect()->intended('/admin/dashboard');
            } elseif ($user->role === 'reviewer') {
                $request->session()->flash('motivational_message', MotivationalMessage::random());
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
        $user = Auth::user();
        $userName = $user?->name ?? 'Admin';

        if ($user && $user->role === 'admin') {
            ActivityLog::record('admin_logout', $user, [], []);
            Cache::forget('admin_session:' . $user->id);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with(
            'logout_success',
            "Sampai jumpa, <strong>{$userName}</strong>! Anda telah berhasil logout."
        );
    }
}
