<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\JournalController;
use App\Http\Controllers\Admin\ReviewAssignmentController as AdminReviewAssignmentController;
use App\Http\Controllers\Admin\ReviewerController;
use App\Http\Controllers\Admin\RewardRedemptionController as AdminRewardRedemptionController;
use App\Http\Controllers\Admin\PointManagementController;
use App\Http\Controllers\Admin\RewardController as AdminRewardController;
use App\Http\Controllers\Admin\LeaderboardController;
use App\Http\Controllers\Admin\MarketingController;
use App\Http\Controllers\Admin\PicController;
use App\Http\Controllers\Reviewer\DashboardController as ReviewerDashboard;
use App\Http\Controllers\Reviewer\TaskController;
use App\Http\Controllers\Reviewer\ReviewResultController;
use App\Http\Controllers\Reviewer\RewardController;
use App\Http\Controllers\Reviewer\ProfileController;
use App\Http\Controllers\Reviewer\LeaderboardController as ReviewerLeaderboardController;
use App\Http\Controllers\Reviewer\CertificateController;
use App\Http\Controllers\Pic\Auth\LoginController as PicLoginController;
use App\Http\Controllers\Pic\AuthorController;
use App\Http\Controllers\ReviewerRegistrationController;
use App\Http\Controllers\Admin\ReviewerRegistrationController as AdminReviewerRegistrationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Root redirect
Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->role === 'admin') {
            return redirect('/admin/dashboard');
        } elseif (Auth::user()->role === 'reviewer') {
            return redirect('/reviewer/dashboard');
        }
        return redirect('/login');
    }
    return redirect('/login');
});

// Test route PIC login
Route::get('/pic-login-test', function () {
    return view('pic.auth.login');
});

// Reviewer Registration (public access)
Route::get('/daftar-reviewer', [ReviewerRegistrationController::class, 'showForm'])->name('reviewer-registration.form');
Route::post('/daftar-reviewer', [ReviewerRegistrationController::class, 'store'])->name('reviewer-registration.store');

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Admin routes
    Route::prefix('admin')->name('admin.')->middleware(\App\Http\Middleware\AdminMiddleware::class)->group(function () {
        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
        Route::get('/export-completed-reviews', [AdminDashboard::class, 'exportCompletedReviews'])->name('export.completed.reviews');
        
        // Journals
        Route::resource('journals', JournalController::class);
        
        // Accreditations
        Route::resource('accreditations', \App\Http\Controllers\Admin\AccreditationController::class);
        
        // Review Assignments
        Route::get('/assignments', [AdminReviewAssignmentController::class, 'index'])->name('assignments.index');
        Route::get('/assignments/create', [AdminReviewAssignmentController::class, 'create'])->name('assignments.create');
        Route::post('/assignments', [AdminReviewAssignmentController::class, 'store'])->name('assignments.store');
        Route::get('/assignments/{assignment}', [AdminReviewAssignmentController::class, 'show'])->name('assignments.show');
        Route::post('/assignments/{assignment}/approve', [AdminReviewAssignmentController::class, 'approve'])->name('assignments.approve');
        Route::post('/assignments/{assignment}/revision', [AdminReviewAssignmentController::class, 'revision'])->name('assignments.revision');
        Route::delete('/assignments/{assignment}', [AdminReviewAssignmentController::class, 'destroy'])->name('assignments.destroy');
        
        // Reviewers
        Route::get('/reviewers', [ReviewerController::class, 'index'])->name('reviewers.index');
        Route::get('/reviewers/{reviewer}', [ReviewerController::class, 'show'])->name('reviewers.show');        
        // Certificates
        Route::resource('certificates', \App\Http\Controllers\Admin\CertificateController::class);        Route::get('/reviewers/{reviewer}/edit', [ReviewerController::class, 'edit'])->name('reviewers.edit');
        Route::put('/reviewers/{reviewer}', [ReviewerController::class, 'update'])->name('reviewers.update');
        
        // Reward Redemptions
        Route::get('/redemptions', [AdminRewardRedemptionController::class, 'index'])->name('redemptions.index');
        Route::get('/redemptions/{redemption}', [AdminRewardRedemptionController::class, 'show'])->name('redemptions.show');
        Route::post('/redemptions/{redemption}/approve', [AdminRewardRedemptionController::class, 'approve'])->name('redemptions.approve');
        Route::post('/redemptions/{redemption}/complete', [AdminRewardRedemptionController::class, 'complete'])->name('redemptions.complete');
        Route::post('/redemptions/{redemption}/reject', [AdminRewardRedemptionController::class, 'reject'])->name('redemptions.reject');
        
        // Point Management
        Route::get('/points', [PointManagementController::class, 'index'])->name('points.index');
        Route::get('/points/create', [PointManagementController::class, 'create'])->name('points.create');
        Route::post('/points', [PointManagementController::class, 'store'])->name('points.store');
        Route::delete('/points/{point}', [PointManagementController::class, 'destroy'])->name('points.destroy');
        
        // Reward Management
        Route::get('/rewards', [AdminRewardController::class, 'index'])->name('rewards.index');
        Route::get('/rewards/create', [AdminRewardController::class, 'create'])->name('rewards.create');
        Route::post('/rewards', [AdminRewardController::class, 'store'])->name('rewards.store');
        Route::get('/rewards/{reward}/edit', [AdminRewardController::class, 'edit'])->name('rewards.edit');
        Route::put('/rewards/{reward}', [AdminRewardController::class, 'update'])->name('rewards.update');
        Route::delete('/rewards/{reward}', [AdminRewardController::class, 'destroy'])->name('rewards.destroy');
        Route::post('/rewards/{reward}/toggle', [AdminRewardController::class, 'toggleStatus'])->name('rewards.toggle');
        
        // Leaderboard
        Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');
        
        // Marketing Management
        Route::resource('marketings', MarketingController::class)->except(['show']);
        
        // PIC Management
        Route::resource('pics', PicController::class)->except(['show']);
        
        // Settings
        Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
        
        // Point Settings
        Route::get('/point-settings', [\App\Http\Controllers\Admin\PointSettingController::class, 'index'])->name('point-settings.index');
        Route::put('/point-settings', [\App\Http\Controllers\Admin\PointSettingController::class, 'update'])->name('point-settings.update');
        
        // Users
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    });

    // Reviewer routes
    Route::prefix('reviewer')->name('reviewer.')->middleware(\App\Http\Middleware\ReviewerMiddleware::class)->group(function () {
        Route::get('/dashboard', [ReviewerDashboard::class, 'index'])->name('dashboard');
        
        // Tasks
        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::get('/tasks/{assignment}', [TaskController::class, 'show'])->name('tasks.show');
        Route::post('/tasks/{assignment}/accept', [TaskController::class, 'accept'])->name('tasks.accept');
        Route::post('/tasks/{assignment}/reject', [TaskController::class, 'reject'])->name('tasks.reject');
        Route::post('/tasks/{assignment}/start', [TaskController::class, 'startProgress'])->name('tasks.start');
        
        // Review Results
        Route::get('/tasks/{assignment}/submit', [ReviewResultController::class, 'create'])->name('results.create');
        Route::post('/tasks/{assignment}/submit', [ReviewResultController::class, 'store'])->name('results.store');
        Route::get('/tasks/{assignment}/download-pdf', [ReviewResultController::class, 'downloadPdf'])->name('results.downloadPdf');
        
        // Rewards
        Route::get('/rewards', [RewardController::class, 'index'])->name('rewards.index');
        Route::post('/rewards/{reward}/redeem', [RewardController::class, 'redeem'])->name('rewards.redeem');
        
        // Profile
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        
        // Leaderboard
        Route::get('/leaderboard', [ReviewerLeaderboardController::class, 'index'])->name('leaderboard.index');
        
        // Certificates
        Route::get('/certificates', [CertificateController::class, 'index'])->name('certificates.index');
        Route::get('/certificates/{assignment}/download', [CertificateController::class, 'download'])->name('certificates.download');
    });

});

// PIC Routes - Separate from main auth
Route::prefix('pic')->group(function () {
    // PIC Login (guest only)
    Route::middleware('guest:pic')->group(function () {
        Route::get('/login', [PicLoginController::class, 'showLoginForm'])->name('pic.login');
        Route::post('/login', [PicLoginController::class, 'login'])->name('pic.login.submit');
    });
    
    // PIC Authenticated routes
    Route::middleware('auth:pic')->group(function () {
        Route::post('/logout', [PicLoginController::class, 'logout'])->name('pic.logout');
        
        // PIC Author routes
        Route::prefix('author')->name('pic.author.')->group(function () {
            Route::get('/dashboard', [AuthorController::class, 'dashboard'])->name('dashboard');
            Route::get('/create', [AuthorController::class, 'create'])->name('create');
            Route::post('/store', [AuthorController::class, 'store'])->name('store');
            Route::get('/{journal}', [AuthorController::class, 'show'])->name('show');
        });
    });
});
