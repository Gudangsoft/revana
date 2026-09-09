<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use App\Models\ReviewRequest;
use App\Models\DeadlineExtensionRequest;
use App\Models\ReviewerRegistration;

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
                // Ditambahkan 9 Sept 2026: sidebar sebelumnya TIDAK PUNYA menu sama
                // sekali ke /admin/reviewer-registrations — pendaftaran reviewer baru
                // via form publik /daftar-reviewer TERSIMPAN dengan benar di tabel
                // reviewer_registrations, tapi admin tidak pernah melihatnya karena
                // tidak ada link ke halaman itu di mana pun (dilaporkan user: "reviewer
                // baru saat register datane belum masuk" — datanya sebenarnya sudah
                // masuk, cuma tidak kelihatan/tidak terjangkau dari menu).
                $pendingReviewerRegistrations = Cache::remember('admin.pending_reviewer_registrations.' . $tenantKey, 300, fn() =>
                    ReviewerRegistration::where('status', 'pending')->count()
                );
            } catch (\Throwable) {
                $pendingReviewRequests = 0;
                $pendingExtensionRequests = 0;
                $pendingReviewerRegistrations = 0;
            }
            $view->with('pendingReviewRequests', $pendingReviewRequests);
            $view->with('pendingExtensionRequests', $pendingExtensionRequests);
            $view->with('pendingReviewerRegistrations', $pendingReviewerRegistrations);
        });
    }
}
