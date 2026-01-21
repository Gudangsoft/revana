<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Marketing;
use App\Models\MarketingPointHistory;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    /**
     * Marketing Login Form
     */
    public function loginForm()
    {
        if (Auth::guard('marketing')->check()) {
            return redirect()->route('marketing.dashboard');
        }
        return view('marketing.login');
    }

    /**
     * Handle Marketing Login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $marketing = Marketing::where('email', $request->email)->first();

        if (!$marketing || !$marketing->is_active) {
            return back()->with('error', 'Email tidak terdaftar atau akun tidak aktif.');
        }

        if (!$marketing->password) {
            return back()->with('error', 'Password belum diatur. Hubungi Admin.');
        }

        if (!Hash::check($request->password, $marketing->password)) {
            return back()->with('error', 'Password salah.');
        }

        Auth::guard('marketing')->login($marketing);

        return redirect()->route('marketing.dashboard');
    }

    /**
     * Handle Marketing Logout
     */
    public function logout()
    {
        Auth::guard('marketing')->logout();
        return redirect()->route('marketing.login');
    }

    /**
     * Marketing Dashboard
     */
    public function dashboard()
    {
        $marketing = Auth::guard('marketing')->user();
        
        $submissions = Submission::where('marketing_id', $marketing->id)
            ->with('journalSlot.journalMaster')
            ->latest('tanggal_submit')
            ->get();
        
        $pointHistories = MarketingPointHistory::where('marketing_id', $marketing->id)
            ->with('submission')
            ->latest()
            ->take(10)
            ->get();
        
        $stats = [
            'total_submissions' => $submissions->count(),
            'submitted' => $submissions->where('status', 'SUBMITTED')->count(),
            'in_process' => $submissions->whereNotIn('status', ['SUBMITTED', 'PUBLISHED', 'REJECTED'])->count(),
            'published' => $submissions->where('status', 'PUBLISHED')->count(),
            'rejected' => $submissions->where('status', 'REJECTED')->count(),
            'total_points' => $marketing->total_points ?? 0,
        ];
        
        return view('marketing.dashboard', compact('marketing', 'submissions', 'pointHistories', 'stats'));
    }

    /**
     * Marketing Submissions List
     */
    public function submissions(Request $request)
    {
        $marketing = Auth::guard('marketing')->user();
        
        $query = Submission::where('marketing_id', $marketing->id)
            ->with('journalSlot.journalMaster');
        
        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_submit', 'like', "%{$search}%")
                  ->orWhere('judul_artikel', 'like', "%{$search}%")
                  ->orWhere('nama_penulis', 'like', "%{$search}%");
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', 'like', $request->status . '%');
        }
        
        $submissions = $query->latest('tanggal_submit')->paginate(10);
        
        return view('marketing.submissions', compact('marketing', 'submissions'));
    }

    /**
     * Marketing Point History
     */
    public function points()
    {
        $marketing = Auth::guard('marketing')->user();
        
        $pointHistories = MarketingPointHistory::where('marketing_id', $marketing->id)
            ->with('submission.journalSlot.journalMaster')
            ->latest()
            ->paginate(20);
        
        return view('marketing.points', compact('marketing', 'pointHistories'));
    }
}
