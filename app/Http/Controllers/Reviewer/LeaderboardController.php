<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use App\Services\ReviewerLeaderboardService;
use Illuminate\Support\Facades\Auth;

class LeaderboardController extends Controller
{
    public function __construct(private ReviewerLeaderboardService $leaderboardService)
    {
    }

    /**
     * Perbaikan 9 Sept 2026: halaman ini tadinya punya salinan query
     * leaderboard sendiri yang sudah kadaluarsa/salah (cuma hitung review
     * sebagai reviewer UTAMA, poin di-sum tanpa pisah EARNED/REDEEMED, rank
     * berdasarkan tier reward yang selalu 0 kalau belum pernah redeem apa
     * pun) — padahal Admin\LeaderboardController sudah punya versi yang
     * diperbaiki. Sekarang keduanya pakai ReviewerLeaderboardService yang
     * sama supaya datanya selalu konsisten & sesuai data real. Lihat
     * docblock ReviewerLeaderboardService untuk detail lengkap tiap bug.
     */
    public function index()
    {
        $currentUser = Auth::user();
        $reviewers = $this->leaderboardService->get();
        $myRank = $reviewers->firstWhere('id', $currentUser->id);

        return view('reviewer.leaderboard.index', compact('reviewers', 'myRank'));
    }
}
