<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Marketing;
use App\Models\Pic;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect ke Google. Query param ?portal=pic atau ?portal=marketing
     * disimpan di session agar callback tahu guard mana yang digunakan.
     */
    public function redirect(string $portal)
    {
        if (! in_array($portal, ['pic', 'marketing'])) {
            return redirect()->route('login')->withErrors(['email' => 'Portal tidak valid.']);
        }

        $loginRoute = $portal === 'pic' ? 'pic.login' : 'marketing.login';

        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect()->route($loginRoute)
                ->withErrors(['email' => 'Fitur Login Google belum dikonfigurasi. Silakan hubungi administrator.']);
        }

        session(['google_portal' => $portal]);

        try {
            return Socialite::driver('google')->redirect();
        } catch (\Exception $e) {
            return redirect()->route($loginRoute)
                ->withErrors(['email' => 'Gagal menghubungi Google. Silakan coba lagi.']);
        }
    }

    /**
     * Callback dari Google. Gunakan session google_portal untuk menentukan
     * guard (pic/marketing) dan tabel yang dicari.
     */
    public function callback()
    {
        $portal = session('google_portal');

        if (! in_array($portal, ['pic', 'marketing'])) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Sesi login Google tidak valid. Silakan coba lagi.']);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            $loginRoute = $portal === 'pic' ? 'pic.login' : 'marketing.login';
            return redirect()->route($loginRoute)
                ->withErrors(['email' => 'Gagal mengambil data dari Google. Silakan coba lagi.']);
        }

        session()->forget('google_portal');

        if ($portal === 'pic') {
            return $this->handlePicLogin($googleUser);
        }

        return $this->handleMarketingLogin($googleUser);
    }

    private function handlePicLogin($googleUser)
    {
        // Cari berdasarkan google_id dulu, lalu fallback ke email (whitelist)
        $pic = Pic::where('google_id', $googleUser->getId())->first()
            ?? Pic::where('email', $googleUser->getEmail())->where('is_active', true)->first();

        if (! $pic) {
            return redirect()->route('pic.login')
                ->withErrors(['email' => 'Akun Google (' . $googleUser->getEmail() . ') tidak terdaftar sebagai PIC aktif.']);
        }

        // Tautkan google_id jika belum ada
        if (! $pic->google_id) {
            $pic->update([
                'google_id' => $googleUser->getId(),
                'avatar'    => $pic->photo ?? $googleUser->getAvatar(),
            ]);
        }

        Auth::guard('pic')->login($pic, true);
        request()->session()->regenerate();

        if ($pic->isBirthdayToday()) {
            return redirect()->route('pic.birthday');
        }

        return redirect()->route('pic.dashboard');
    }

    private function handleMarketingLogin($googleUser)
    {
        $marketing = Marketing::where('google_id', $googleUser->getId())->first()
            ?? Marketing::where('email', $googleUser->getEmail())->where('is_active', true)->first();

        if (! $marketing) {
            return redirect()->route('marketing.login')
                ->withErrors(['email' => 'Akun Google (' . $googleUser->getEmail() . ') tidak terdaftar sebagai Marketing aktif.']);
        }

        if (! $marketing->google_id) {
            $marketing->update([
                'google_id' => $googleUser->getId(),
                'avatar'    => $marketing->photo ?? $googleUser->getAvatar(),
            ]);
        }

        Auth::guard('marketing')->login($marketing, true);
        request()->session()->regenerate();

        if ($marketing->isBirthdayToday()) {
            return redirect()->route('marketing.birthday');
        }

        return redirect()->route('marketing.dashboard');
    }
}
