<?php

namespace App\Http\Controllers\Pic;

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
        $pic = Auth::guard('pic')->user();
        return view('pic.profile.edit', compact('pic'));
    }

    public function update(Request $request)
    {
        $pic = Auth::guard('pic')->user();

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'username'      => ['required','string','max:255', Rule::unique('pics','username')->ignore($pic->id)],
            'email'         => ['required','email','max:255',
                                Rule::unique('pics','email')->ignore($pic->id),
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
            if ($pic->photo && Storage::disk('public')->exists($pic->photo)) {
                Storage::disk('public')->delete($pic->photo);
            }
            $file     = $request->file('photo');
            $filename = 'pic_' . $pic->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path     = $file->storeAs('photos/pics', $filename, 'public');
            $validated['photo'] = $path;
        }

        $pic->update($validated);

        return redirect()->route('pic.profile.edit')
            ->with('success', 'Profile berhasil diperbarui');
    }

    public function updatePassword(Request $request)
    {
        $pic = Auth::guard('pic')->user();

        $validated = $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:6|confirmed',
        ], [
            'current_password.required' => 'Password saat ini harus diisi',
            'new_password.required'     => 'Password baru harus diisi',
            'new_password.min'          => 'Password baru minimal 6 karakter',
            'new_password.confirmed'    => 'Konfirmasi password tidak cocok',
        ]);

        if (!Hash::check($validated['current_password'], $pic->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah']);
        }

        $pic->password = Hash::make($validated['new_password']);
        $pic->save();

        return redirect()->route('pic.profile.edit')
            ->with('success', 'Password berhasil diubah');
    }

    public function birthday()
    {
        $pic  = Auth::guard('pic')->user();
        $data = session('birthday_celebration');

        // Allow access only on birthday or if session is set
        if (!$data && !$pic->isBirthdayToday()) {
            return redirect()->route('pic.dashboard');
        }

        $name  = $data['name']  ?? $pic->name;
        $umur  = $data['umur']  ?? $pic->umur;
        $dashboard = route('pic.dashboard');

        $wishes = BirthdayWish::where('recipient_type', 'pic')
            ->where('recipient_id', $pic->id)
            ->where('wish_year', now()->year)
            ->latest()
            ->get();

        return view('birthday', compact('name', 'umur', 'dashboard', 'wishes'));
    }
}
