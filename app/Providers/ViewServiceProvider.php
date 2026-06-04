<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use App\Models\ReviewRequest;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Cached 5 menit — runs on every admin view render otherwise
        View::composer('admin.*', function ($view) {
            $pendingReviewRequests = Cache::remember('admin.pending_review_requests', 300, fn() =>
                ReviewRequest::where('status', 'pending')->count()
            );
            $view->with('pendingReviewRequests', $pendingReviewRequests);
        });
    }
}
