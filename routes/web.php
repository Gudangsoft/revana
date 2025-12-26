<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\JournalController;
use App\Http\Controllers\Admin\ReviewAssignmentController as AdminReviewAssignmentController;
use App\Http\Controllers\Admin\ReviewerController;
use App\Http\Controllers\Admin\RewardRedemptionController as AdminRewardRedemptionController;
use App\Http\Controllers\Admin\PointManagementController;
use App\Http\Controllers\Admin\RewardController as AdminRewardController;
use App\Http\Controllers\Reviewer\DashboardController as ReviewerDashboard;
use App\Http\Controllers\Reviewer\TaskController;
use App\Http\Controllers\Reviewer\ReviewResultController;
use App\Http\Controllers\Reviewer\RewardController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect('/login');
    });
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Admin routes
    Route::prefix('admin')->name('admin.')->middleware(\App\Http\Middleware\AdminMiddleware::class)->group(function () {
        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
        
        // Journals
        Route::resource('journals', JournalController::class);
        
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
        
        // Rewards
        Route::get('/rewards', [RewardController::class, 'index'])->name('rewards.index');
        Route::post('/rewards/{reward}/redeem', [RewardController::class, 'redeem'])->name('rewards.redeem');
    });
});
