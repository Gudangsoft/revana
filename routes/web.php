<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\JournalController;
use App\Http\Controllers\Admin\JournalMasterController;
use App\Http\Controllers\Admin\JournalSlotController;
use App\Http\Controllers\Admin\SubmissionController;
use App\Http\Controllers\Admin\ReviewAssignmentController as AdminReviewAssignmentController;
use App\Http\Controllers\Admin\ReviewerController;
use App\Http\Controllers\Admin\RewardRedemptionController as AdminRewardRedemptionController;
use App\Http\Controllers\Admin\PointManagementController;
use App\Http\Controllers\Admin\RewardController as AdminRewardController;
use App\Http\Controllers\Admin\LeaderboardController;
use App\Http\Controllers\Admin\MarketingController;
use App\Http\Controllers\Admin\PicController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Reviewer\DashboardController as ReviewerDashboard;
use App\Http\Controllers\Reviewer\TaskController;
use App\Http\Controllers\Reviewer\ReviewResultController;
use App\Http\Controllers\Reviewer\RewardController;
use App\Http\Controllers\Reviewer\ProfileController;
use App\Http\Controllers\Reviewer\LeaderboardController as ReviewerLeaderboardController;
use App\Http\Controllers\Reviewer\CertificateController;
use App\Http\Controllers\Pic\Auth\LoginController as PicLoginController;
use App\Http\Controllers\Pic\AuthorController;
use App\Http\Controllers\Pic\JournalManagementController as PicJournalController;
use App\Http\Controllers\ReviewerRegistrationController;
use App\Http\Controllers\Admin\ReviewerRegistrationController as AdminReviewerRegistrationController;
use App\Http\Controllers\ReviewRequestController;
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
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1'); // 5 attempts per minute
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Admin routes
    Route::prefix('admin')->name('admin.')->middleware(\App\Http\Middleware\AdminMiddleware::class)->group(function () {
        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
        Route::get('/export-completed-reviews', [AdminDashboard::class, 'exportCompletedReviews'])->name('export.completed.reviews');
        
        // Monitoring
        Route::get('/monitoring', [AdminReviewAssignmentController::class, 'monitoring'])->name('monitoring');
        
        // Journals
        Route::get('/journals/monitoring', [JournalController::class, 'monitoringSlots'])->name('journals.monitoring');
        Route::resource('journals', JournalController::class);
        
        // Journal Masters (Data Jurnal)
        Route::get('/journal-masters/export', [JournalMasterController::class, 'export'])->name('journal-masters.export');
        Route::post('/journal-masters/import', [JournalMasterController::class, 'import'])->name('journal-masters.import');
        Route::get('/journal-masters/template', [JournalMasterController::class, 'downloadTemplate'])->name('journal-masters.template');
        Route::patch('/journal-masters/{journalMaster}/toggle-active', [JournalMasterController::class, 'toggleActive'])->name('journal-masters.toggle-active');
        Route::resource('journal-masters', JournalMasterController::class);
        
        // Journal Slots (Data Slot)
        Route::get('/journal-slots/export', [JournalSlotController::class, 'export'])->name('journal-slots.export');
        Route::post('/journal-slots/import', [JournalSlotController::class, 'import'])->name('journal-slots.import');
        Route::get('/journal-slots/template', [JournalSlotController::class, 'downloadTemplate'])->name('journal-slots.template');
        Route::get('/journal-slots/monitoring', [JournalSlotController::class, 'monitoring'])->name('journal-slots.monitoring');
        Route::get('/journal-slots/get-by-journal', [JournalSlotController::class, 'getByJournal'])->name('journal-slots.get-by-journal');
        Route::patch('/journal-slots/{journalSlot}/toggle-active', [JournalSlotController::class, 'toggleActive'])->name('journal-slots.toggle-active');
        Route::resource('journal-slots', JournalSlotController::class);
        
        // Submissions (Data Submit & Proses Workflow)
        Route::get('/submissions/monitoring', [SubmissionController::class, 'monitoring'])->name('submissions.monitoring');
        Route::get('/submissions/export', [SubmissionController::class, 'export'])->name('submissions.export');
        Route::get('/submissions/import', [SubmissionController::class, 'importForm'])->name('submissions.import.form');
        Route::post('/submissions/import', [SubmissionController::class, 'import'])->name('submissions.import');
        Route::get('/submissions/template', [SubmissionController::class, 'downloadTemplate'])->name('submissions.template');
        Route::post('/submissions/bulk-assign', [SubmissionController::class, 'bulkAssign'])->name('submissions.bulk-assign');
        Route::post('/submissions/bulk-assign-with-credentials', [SubmissionController::class, 'bulkAssignWithCredentials'])->name('submissions.bulk-assign-with-credentials');
        Route::post('/submissions/quick-assign', [SubmissionController::class, 'quickAssign'])->name('submissions.quick-assign');
        Route::post('/submissions/quick-assign-marketing', [SubmissionController::class, 'quickAssignMarketing'])->name('submissions.quick-assign-marketing');
        Route::post('/submissions/quick-update-credential', [SubmissionController::class, 'quickUpdateCredential'])->name('submissions.quick-update-credential');
        Route::get('/submissions/{submission}/process', [SubmissionController::class, 'process'])->name('submissions.process');
        Route::get('/submissions/{submission}/history', [SubmissionController::class, 'history'])->name('submissions.history');
        Route::post('/submissions/{submission}/update-process', [SubmissionController::class, 'updateProcess'])->name('submissions.update-process');
        Route::post('/submissions/{submission}/validate-step', [SubmissionController::class, 'validateStep'])->name('submissions.validate-step');
        Route::post('/submissions/{submission}/request-revision', [SubmissionController::class, 'requestRevision'])->name('submissions.request-revision');
        Route::post('/submissions/{submission}/submit-revision', [SubmissionController::class, 'submitRevision'])->name('submissions.submit-revision');
        Route::post('/submissions/{submission}/update-reviewer-notes', [SubmissionController::class, 'updateReviewerNotes'])->name('submissions.update-reviewer-notes');
        Route::resource('submissions', SubmissionController::class);
        
        // Articles
        Route::get('/articles/monitoring', [\App\Http\Controllers\Admin\ArticleController::class, 'monitoring'])->name('articles.monitoring');
        Route::resource('articles', \App\Http\Controllers\Admin\ArticleController::class);
        Route::put('/articles/{article}/submission', [\App\Http\Controllers\Admin\ArticleController::class, 'updateSubmission'])->name('articles.update-submission');
        Route::put('/articles/{article}/review', [\App\Http\Controllers\Admin\ArticleController::class, 'updateReview'])->name('articles.update-review');
        Route::put('/articles/{article}/revision', [\App\Http\Controllers\Admin\ArticleController::class, 'updateRevision'])->name('articles.update-revision');
        Route::put('/articles/{article}/acceptance', [\App\Http\Controllers\Admin\ArticleController::class, 'updateAcceptance'])->name('articles.update-acceptance');
        Route::put('/articles/{article}/copyediting', [\App\Http\Controllers\Admin\ArticleController::class, 'updateCopyediting'])->name('articles.update-copyediting');
        Route::put('/articles/{article}/production', [\App\Http\Controllers\Admin\ArticleController::class, 'updateProduction'])->name('articles.update-production');
        Route::put('/articles/{article}/publication', [\App\Http\Controllers\Admin\ArticleController::class, 'updatePublication'])->name('articles.update-publication');
        
        // Accreditations
        Route::get('/accreditations/export', [\App\Http\Controllers\Admin\AccreditationController::class, 'export'])->name('accreditations.export');
        Route::post('/accreditations/import', [\App\Http\Controllers\Admin\AccreditationController::class, 'import'])->name('accreditations.import');
        Route::get('/accreditations/template', [\App\Http\Controllers\Admin\AccreditationController::class, 'downloadTemplate'])->name('accreditations.template');
        Route::resource('accreditations', \App\Http\Controllers\Admin\AccreditationController::class);
        
        // Review Assignments
        Route::get('/assignments', [AdminReviewAssignmentController::class, 'index'])->name('assignments.index');
        Route::get('/assignments/create', [AdminReviewAssignmentController::class, 'create'])->name('assignments.create');
        Route::post('/assignments', [AdminReviewAssignmentController::class, 'store'])->name('assignments.store');
        Route::post('/assignments/batch', [AdminReviewAssignmentController::class, 'storeBatch'])->name('assignments.store-batch');
        Route::get('/assignments/{assignment}', [AdminReviewAssignmentController::class, 'show'])->name('assignments.show');
        Route::get('/assignments/{assignment}/download-pdf/{reviewResult}', [AdminReviewAssignmentController::class, 'downloadPdf'])->name('assignments.download-pdf');
        Route::post('/assignments/{assignment}/approve', [AdminReviewAssignmentController::class, 'approve'])->name('assignments.approve');
        Route::post('/assignments/{assignment}/revision', [AdminReviewAssignmentController::class, 'revision'])->name('assignments.revision');
        Route::delete('/assignments/{assignment}', [AdminReviewAssignmentController::class, 'destroy'])->name('assignments.destroy');
        
        // Reviewers
        Route::get('/reviewers', [ReviewerController::class, 'index'])->name('reviewers.index');
        Route::get('/reviewers/export', [ReviewerController::class, 'export'])->name('reviewers.export');
        Route::get('/reviewers/{reviewer}', [ReviewerController::class, 'show'])->name('reviewers.show');        
        // Certificates
        Route::resource('certificates', \App\Http\Controllers\Admin\CertificateController::class);        Route::get('/reviewers/{reviewer}/edit', [ReviewerController::class, 'edit'])->name('reviewers.edit');
        Route::put('/reviewers/{reviewer}', [ReviewerController::class, 'update'])->name('reviewers.update');
        Route::post('/reviewers/{reviewer}/reset-password', [ReviewerController::class, 'resetPassword'])->name('reviewers.reset-password');
        Route::post('/reviewers/{reviewer}/login-as', [ReviewerController::class, 'loginAs'])->name('reviewers.login-as');
        
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
        Route::post('/marketings/{marketing}/login-as', [MarketingController::class, 'loginAs'])->name('marketings.login-as');
        
        // Marketing Point Report
        Route::get('/marketing-points', [\App\Http\Controllers\Admin\MarketingPointReportController::class, 'index'])->name('marketing-points.index');
        Route::get('/marketing-points/{marketing}', [\App\Http\Controllers\Admin\MarketingPointReportController::class, 'show'])->name('marketing-points.show');
        Route::post('/marketing-points/{marketing}/adjust', [\App\Http\Controllers\Admin\MarketingPointReportController::class, 'adjustPoints'])->name('marketing-points.adjust');
        
        // PIC Management
        Route::resource('pics', PicController::class)->except(['show']);
        Route::get('/pics-export', [PicController::class, 'export'])->name('pics.export');
        Route::post('/pics-import', [PicController::class, 'import'])->name('pics.import');
        Route::get('/pics-template', [PicController::class, 'downloadTemplate'])->name('pics.template');
        
        // PIC Point Report
        Route::get('/pic-points', [\App\Http\Controllers\Admin\PicPointReportController::class, 'index'])->name('pic-points.index');
        Route::get('/pic-points/export', [\App\Http\Controllers\Admin\PicPointReportController::class, 'export'])->name('pic-points.export');
        Route::get('/pic-points/{pic}', [\App\Http\Controllers\Admin\PicPointReportController::class, 'show'])->name('pic-points.show');
        Route::post('/pic-points/{pic}/adjust', [\App\Http\Controllers\Admin\PicPointReportController::class, 'adjustPoints'])->name('pic-points.adjust');
        Route::post('/pics/{pic}/login-as', [PicController::class, 'loginAs'])->name('pics.login-as');
        
        // Field of Study Management
        Route::resource('field-of-studies', \App\Http\Controllers\Admin\FieldOfStudyController::class)->except(['show']);
        Route::post('/field-of-studies/{fieldOfStudy}/toggle', [\App\Http\Controllers\Admin\FieldOfStudyController::class, 'toggleStatus'])->name('field-of-studies.toggle');
        Route::post('/field-of-studies-import', [\App\Http\Controllers\Admin\FieldOfStudyController::class, 'import'])->name('field-of-studies.import');
        Route::get('/field-of-studies-template', [\App\Http\Controllers\Admin\FieldOfStudyController::class, 'downloadTemplate'])->name('field-of-studies.template');
        Route::delete('/field-of-studies-bulk-delete', [\App\Http\Controllers\Admin\FieldOfStudyController::class, 'bulkDelete'])->name('field-of-studies.bulk-delete');
        
        // Settings
        Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
        
        // Email Settings
        Route::get('/email-settings', [\App\Http\Controllers\Admin\EmailSettingController::class, 'index'])->name('email-settings.index');
        Route::put('/email-settings', [\App\Http\Controllers\Admin\EmailSettingController::class, 'update'])->name('email-settings.update');
        Route::post('/email-settings/test-email', [\App\Http\Controllers\Admin\EmailSettingController::class, 'testEmail'])->name('email-settings.test-email');
        
        // Point Settings
        Route::get('/point-settings', [\App\Http\Controllers\Admin\PointSettingController::class, 'index'])->name('point-settings.index');
        Route::put('/point-settings', [\App\Http\Controllers\Admin\PointSettingController::class, 'update'])->name('point-settings.update');
        
        // Users
        Route::post('/users/broadcast-email', [\App\Http\Controllers\Admin\UserController::class, 'broadcastEmail'])->name('users.broadcast-email');
        Route::post('/users/{user}/reset-password', [\App\Http\Controllers\Admin\UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
        
        // Profile
        Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [AdminProfileController::class, 'updatePassword'])->name('profile.password.update');
        
        // Review Requests Management (Admin)
        Route::get('/review-requests', [ReviewRequestController::class, 'index'])->name('review-requests.index');
        Route::get('/review-requests/export/excel', [ReviewRequestController::class, 'export'])->name('review-requests.export');
        Route::get('/review-requests/{reviewRequest}', [ReviewRequestController::class, 'show'])->name('review-requests.show');
        Route::post('/review-requests/{reviewRequest}/approve', [ReviewRequestController::class, 'approve'])->name('review-requests.approve');
        Route::post('/review-requests/{reviewRequest}/reject', [ReviewRequestController::class, 'reject'])->name('review-requests.reject');
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
        Route::post('/tasks/{assignment}/upload-revision', [ReviewResultController::class, 'uploadRevision'])->name('results.uploadRevision');
        
        // Rewards
        Route::get('/rewards', [RewardController::class, 'index'])->name('rewards.index');
        Route::post('/rewards/{reward}/redeem', [RewardController::class, 'redeem'])->name('rewards.redeem');
        
        // Profile
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
        
        // Leaderboard
        Route::get('/leaderboard', [ReviewerLeaderboardController::class, 'index'])->name('leaderboard.index');
        
        // Certificates
        Route::get('/certificates', [CertificateController::class, 'index'])->name('certificates.index');
        Route::get('/certificates/{assignment}/view', [CertificateController::class, 'view'])->name('certificates.view');
        Route::get('/certificates/{assignment}/download', [CertificateController::class, 'download'])->name('certificates.download');
        
        // Review Requests (Reviewer)
        Route::get('/review-requests', [ReviewRequestController::class, 'myRequests'])->name('review-requests.my-requests');
        Route::get('/review-requests/create', [ReviewRequestController::class, 'create'])->name('review-requests.create');
        Route::post('/review-requests', [ReviewRequestController::class, 'store'])->name('review-requests.store');
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
        
        // PIC Dashboard - redirect to author dashboard
        Route::get('/dashboard', [AuthorController::class, 'dashboard'])->name('pic.dashboard');
        
        // PIC Author routes
        Route::prefix('author')->name('pic.author.')->group(function () {
            Route::get('/dashboard', [AuthorController::class, 'dashboard'])->name('dashboard');
            Route::get('/create', [AuthorController::class, 'create'])->name('create');
            Route::post('/store', [AuthorController::class, 'store'])->name('store');
            Route::get('/{journal}', [AuthorController::class, 'show'])->name('show');
        });
        
        // PIC Pengelolaan Jurnal (menggunakan controller PIC)
        Route::prefix('journals')->name('pic.journals.')->group(function () {
            Route::get('/', [PicJournalController::class, 'journalsIndex'])->name('index');
            Route::get('/create', [PicJournalController::class, 'journalsCreate'])->name('create');
            Route::post('/', [PicJournalController::class, 'journalsStore'])->name('store');
            Route::get('/{journal}/edit', [PicJournalController::class, 'journalsEdit'])->name('edit');
            Route::put('/{journal}', [PicJournalController::class, 'journalsUpdate'])->name('update');
            Route::delete('/{journal}', [PicJournalController::class, 'journalsDestroy'])->name('destroy');
        });
        
        Route::prefix('journal-slots')->name('pic.journal-slots.')->group(function () {
            Route::get('/', [PicJournalController::class, 'slotsIndex'])->name('index');
            Route::get('/monitoring', [PicJournalController::class, 'slotsMonitoring'])->name('monitoring');
            Route::get('/create', [PicJournalController::class, 'slotsCreate'])->name('create');
            Route::post('/', [PicJournalController::class, 'slotsStore'])->name('store');
            Route::get('/{slot}/edit', [PicJournalController::class, 'slotsEdit'])->name('edit');
            Route::put('/{slot}', [PicJournalController::class, 'slotsUpdate'])->name('update');
            Route::delete('/{slot}', [PicJournalController::class, 'slotsDestroy'])->name('destroy');
        });
        
        Route::prefix('submissions')->name('pic.submissions.')->group(function () {
            Route::get('/', [PicJournalController::class, 'submissionsIndex'])->name('index');
            Route::get('/monitoring', [PicJournalController::class, 'submissionsMonitoring'])->name('monitoring');
            Route::post('/update-credential', [PicJournalController::class, 'updateCredential'])->name('update-credential');
            Route::post('/toggle-valid', [PicJournalController::class, 'toggleValid'])->name('toggle-valid');
            Route::get('/create', [PicJournalController::class, 'submissionsCreate'])->name('create');
            Route::post('/', [PicJournalController::class, 'submissionsStore'])->name('store');
            Route::get('/{submission}', [PicJournalController::class, 'submissionsShow'])->name('show');
            Route::get('/{submission}/process', [PicJournalController::class, 'submissionsProcess'])->name('process');
            Route::post('/{submission}/submit-work', [PicJournalController::class, 'submitWork'])->name('submit-work');
            Route::post('/{submission}/request-revision', [PicJournalController::class, 'requestRevision'])->name('request-revision');
        });
        
        // Akreditasi
        Route::prefix('accreditations')->name('pic.accreditations.')->group(function () {
            Route::get('/', [PicJournalController::class, 'accreditationsIndex'])->name('index');
        });
        
        // Tugas Saya (My Tasks)
        Route::get('/my-tasks', [PicJournalController::class, 'myTasks'])->name('pic.my-tasks.index');
        
        // Point Saya
        Route::get('/points', [\App\Http\Controllers\Pic\PicPointController::class, 'index'])->name('pic.points.index');
        
        // Reviewers
        Route::get('/reviewers', [PicJournalController::class, 'reviewersIndex'])->name('pic.reviewers.index');
        Route::post('/reviewers/{reviewer}/login-as', [PicJournalController::class, 'loginAsReviewer'])->name('pic.reviewers.login-as');
    });
});

// =====================================================
// MARKETING ROUTES
// =====================================================
use App\Http\Controllers\Marketing\DashboardController as MarketingDashboardController;

Route::prefix('marketing')->group(function () {
    // Guest routes
    Route::get('/login', [MarketingDashboardController::class, 'loginForm'])->name('marketing.login');
    Route::post('/login', [MarketingDashboardController::class, 'login'])->name('marketing.login.submit');
    
    // Authenticated routes
    Route::middleware('auth:marketing')->group(function () {
        Route::post('/logout', [MarketingDashboardController::class, 'logout'])->name('marketing.logout');
        Route::get('/dashboard', [MarketingDashboardController::class, 'dashboard'])->name('marketing.dashboard');
        Route::get('/submissions', [MarketingDashboardController::class, 'submissions'])->name('marketing.submissions');
        Route::get('/submissions/create', [MarketingDashboardController::class, 'createSubmission'])->name('marketing.submissions.create');
        Route::post('/submissions', [MarketingDashboardController::class, 'storeSubmission'])->name('marketing.submissions.store');
        Route::get('/submissions/{submission}', [MarketingDashboardController::class, 'showSubmission'])->name('marketing.submissions.show');
        Route::get('/points', [MarketingDashboardController::class, 'points'])->name('marketing.points');
        
        // Journal Management
        Route::get('/journals', [MarketingDashboardController::class, 'journalsIndex'])->name('marketing.journals.index');
        Route::get('/journal-slots', [MarketingDashboardController::class, 'journalSlotsIndex'])->name('marketing.journal-slots.index');
    });
});
