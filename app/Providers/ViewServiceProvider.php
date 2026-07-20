<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use App\Models\ReviewRequest;
use App\Models\DeadlineExtensionRequest;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Cached 5 menit — runs on every admin view render otherwise
        View::composer('admin.*', function ($view) {
            try {
                $tenantKey = app()->bound('tenant') ? app('tenant')->subdomain : 'master';
                $pendingReviewRequests = Cache::remember('admin.pending_review_requests.' . $tenantKey, 300, fn() =>
                    ReviewRequest::where('status', 'pending')->count()
                );
                $pendingExtensionRequests = Cache::remember('admin.pending_extension_requests.' . $tenantKey, 300, fn() =>
                    DeadlineExtensionRequest::where('status', 'PENDING')->count()
                );
            } catch (\Throwable) {
                $pendingReviewRequests = 0;
                $pendingExtensionRequests = 0;
            }
            $view->with('pendingReviewRequests', $pendingReviewRequests);
            $view->with('pendingExtensionRequests', $pendingExtensionRequests);
        });
    }
}
