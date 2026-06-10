<?php

namespace App\Http\Controllers\Pic\Auth;

use App\Helpers\MotivationalMessage;
use App\Http\Controllers\Controller;
use App\Models\Pic;
use App\Services\WaNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        $a = rand(1, 9);
        $b = rand(1, 9);
        session(['captcha_pic' => $a + $b]);
        $captcha_question = "$a + $b";

        return view('pic.auth.login', compact('captcha_question'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ((int) $request->input('captcha_answer') !== (int) session('captcha_pic')) {
            return back()->withErrors(['email' => 'Jawaban verifikasi salah. Silakan coba lagi.'])->withInput($request->only('email'));
        }
        session()->forget('captcha_pic');

        $email    = $request->input('email');
        $password = $request->input('password');
        $remember = $request->filled('remember');

        $pic = Pic::where('email', $email)->first();

        if (!$pic) {
            return back()->withErrors(['email' => 'PIC dengan email ini tidak ditemukan.'])->withInput($request->only('email'));
        }

        if (!$pic->is_active) {
            return back()->withErrors(['email' => 'Akun Anda tidak aktif. Hubungi administrator.'])->withInput($request->only('email'));
        }

        if (!Hash::check($password, $pic->password)) {
            Log::warning('PIC login failed: wrong password', ['email' => $email, 'ip' => $request->ip()]);
            return back()->withErrors(['email' => 'Password salah.'])->withInput($request->only('email'));
        }

        Auth::guard('pic')->login($pic, $remember);
        $request->session()->regenerate();

        // Cek ulang tahun
        if ($pic->isBirthdayToday()) {
            $umur = $pic->umur ?? 0;

            $request->session()->flash('birthday_celebration', [
                'name' => $pic->name,
                'umur' => $umur,
            ]);

            // Kirim WA
            try {
                app(WaNotificationService::class)->notifyBirthday($pic);
            } catch (\Throwable $e) {
                Log::error('Birthday WA gagal', ['pic' => $pic->id, 'error' => $e->getMessage()]);
            }

            // Kirim email
            if ($pic->email) {
                try {
                    $name = $pic->name;
                    $body = "Selamat Ulang Tahun ke-{$umur}, {$name}!\n\n"
                        . "Di hari yang istimewa ini, seluruh Tim SIPERA mengucapkan:\n"
                        . "✨ Semoga panjang umur & selalu sehat\n"
                        . "🌟 Semua impian dan cita-citamu terwujud\n"
                        . "💪 Semakin sukses dalam setiap langkahmu\n\n"
                        . "Tetap semangat berkarya!\n\n— Tim SIPERA";

                    Mail::raw($body, function ($m) use ($pic) {
                        $m->to($pic->email)->subject("🎂 Selamat Ulang Tahun, {$pic->name}!");
                    });
                } catch (\Throwable $e) {
                    Log::error('Birthday email gagal', ['pic' => $pic->id, 'error' => $e->getMessage()]);
                }
            }

            return redirect()->route('pic.birthday');
        }

        $request->session()->flash('motivational_message', MotivationalMessage::random());
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
