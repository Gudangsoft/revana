<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReviewerLeaderboardService;

class LeaderboardController extends Controller
{
    public function __construct(private ReviewerLeaderboardService $leaderboardService)
    {
    }

    public function index()
    {
        $reviewers = $this->leaderboardService->get();

        return view('admin.leaderboard.index', compact('reviewers'));
    }
}
