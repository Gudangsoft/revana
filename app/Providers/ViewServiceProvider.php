<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\ReviewRequest;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share pending review requests count with all admin views
        View::composer('admin.*', function ($view) {
            $pendingReviewRequests = ReviewRequest::where('status', 'pending')->count();
            $view->with('pendingReviewRequests', $pendingReviewRequests);
        });
    }
}
