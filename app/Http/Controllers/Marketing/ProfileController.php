<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\BirthdayWish;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        $marketing = Auth::guard('marketing')->user();
        return view('marketing.profile.edit', compact('marketing'));
    }

    public function update(Request $request)
    {
        $marketing = Auth::guard('marketing')->user();

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'username'      => ['required','string','max:255', Rule::unique('marketings','username')->ignore($marketing->id)],
            'email'         => ['required','email','max:255',
                                Rule::unique('marketings','email')->ignore($marketing->id),
                                function ($attr, $value, $fail) {
                                    if (!str_ends_with(strtolower($value), '@gmail.com')) {
                                        $fail('Email harus berakhiran @gmail.com (gunakan Gmail aktif).');
                                    }
                                }],
            'phone'         => 'nullable|string|max:20',
            'tanggal_lahir' => 'required|date|before:today',
            'photo'         => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ], [
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.before'   => 'Tanggal lahir harus sebelum hari ini.',
        ]);

        if ($request->hasFile('photo')) {
            if ($marketing->photo && Storage::disk('public')->exists($marketing->photo)) {
                Storage::disk('public')->delete($marketing->photo);
            }
            $file     = $request->file('photo');
            $filename = 'marketing_' . $marketing->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path     = $file->storeAs('photos/marketings', $filename, 'public');
            $validated['photo'] = $path;
        }

        $marketing->update($validated);

        return redirect()->route('marketing.profile.edit')
            ->with('success', 'Profile berhasil diperbarui');
    }

    public function updatePassword(Request $request)
    {
        $marketing = Auth::guard('marketing')->user();

        $validated = $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:6|confirmed',
        ], [
            'current_password.required' => 'Password saat ini harus diisi',
            'new_password.required'     => 'Password baru harus diisi',
            'new_password.min'          => 'Password baru minimal 6 karakter',
            'new_password.confirmed'    => 'Konfirmasi password tidak cocok',
        ]);

        if (!Hash::check($validated['current_password'], $marketing->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah']);
        }

        $marketing->password = Hash::make($validated['new_password']);
        $marketing->save();

        return redirect()->route('marketing.profile.edit')
            ->with('success', 'Password berhasil diubah');
    }

    public function birthday()
    {
        $marketing = Auth::guard('marketing')->user();
        $data      = session('birthday_celebration');

        if (!$data && !$marketing->isBirthdayToday()) {
            return redirect()->route('marketing.dashboard');
        }

        $name      = $data['name']  ?? $marketing->name;
        $umur      = $data['umur']  ?? $marketing->umur;
        $dashboard = route('marketing.dashboard');

        $wishes = BirthdayWish::where('recipient_type', 'marketing')
            ->where('recipient_id', $marketing->id)
            ->where('wish_year', now()->year)
            ->latest()
            ->get();

        return view('birthday', compact('name', 'umur', 'dashboard', 'wishes'));
    }
}
