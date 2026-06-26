<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        
        $users = User::query()
            ->when($search, function($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('role', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(request()->input('per_page', 15))
            ->appends(['search' => $search]);
            
        return view('admin.users.index', compact('users', 'search'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,reviewer,pic,pic_reviewer',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        
        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil ditambahkan');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:admin,reviewer,pic,pic_reviewer',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil diperbarui');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil dihapus');
    }

    public function loginAs(User $user)
    {
        if ($user->isAdmin()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Tidak dapat login sebagai akun admin lain.');
        }

        // Simpan ID admin asli di session
        session(['admin_user_impersonating' => Auth::id()]);

        Auth::login($user);

        $redirect = match(true) {
            $user->isReviewer()    => redirect()->route('reviewer.dashboard'),
            $user->isPicReviewer() => redirect()->route('admin.pic-reviewer.dashboard'),
            default                => redirect()->route('admin.dashboard'),
        };

        return $redirect->with('info', 'Anda sekarang login sebagai ' . $user->name . '. Klik "Kembali ke Admin" untuk keluar.');
    }

    public function returnToAdmin()
    {
        $adminId = session('admin_user_impersonating');

        if (!$adminId) {
            return redirect()->route('admin.dashboard');
        }

        $admin = User::find($adminId);

        if (!$admin || !$admin->isAdmin()) {
            Auth::logout();
            session()->flush();
            return redirect()->route('login')->with('error', 'Sesi admin tidak valid.');
        }

        Auth::login($admin);
        session()->forget('admin_user_impersonating');

        return redirect()->route('admin.users.index')
            ->with('success', 'Berhasil kembali ke akun admin.');
    }

    public function resetPassword(User $user)
    {
        // Generate random password
        $newPassword = 'Password123!';
        
        // Update user password
        $user->password = Hash::make($newPassword);
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', "Password untuk {$user->name} berhasil direset menjadi: {$newPassword}");
    }
    
    public function broadcastEmail(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);
        
        $users = User::whereNotNull('email')->get();
        $successCount = 0;
        $failedCount = 0;
        
        foreach ($users as $user) {
            try {
                \Mail::raw($request->message, function($mail) use ($user, $request) {
                    $mail->to($user->email)
                         ->subject($request->subject);
                });
                $successCount++;
            } catch (\Exception $e) {
                $failedCount++;
                \Log::error('Failed to send broadcast email to ' . $user->email . ': ' . $e->getMessage());
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => "Email berhasil dikirim ke {$successCount} pengguna" . ($failedCount > 0 ? ", {$failedCount} gagal" : "")
        ]);
    }
}
