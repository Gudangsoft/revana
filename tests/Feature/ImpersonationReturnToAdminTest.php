<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Regresi 10 Sept 2026: setelah admin "Login As" reviewer, tombol "Kembali ke
 * Admin" mengarah ke /admin/users/return-to-admin yang berada di dalam grup
 * middleware admin — padahal saat impersonasi guard web BUKAN admin lagi, jadi
 * AdminMiddleware menolak dengan 403 dan admin terjebak tidak bisa balik.
 *
 * Perbaikan: rute pindah ke luar grup admin (name: impersonation.return, cuma
 * butuh auth), dan controller-nya menerima dua nama key session karena ada 2
 * pintu masuk impersonasi (UserController::loginAs & ReviewerController::loginAs).
 */
class ImpersonationReturnToAdminTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::create([
            'name' => 'Admin Asli', 'email' => 'admin-' . uniqid() . '@example.test',
            'password' => bcrypt('password'), 'role' => 'admin',
        ]);
    }

    private function makeReviewer(): User
    {
        return User::create([
            'name' => 'Reviewer Target', 'email' => 'rev-' . uniqid() . '@example.test',
            'password' => bcrypt('password'), 'role' => 'reviewer',
            'total_points' => 0, 'available_points' => 0, 'completed_reviews' => 0,
        ]);
    }

    public function test_return_route_is_not_gated_by_admin_middleware(): void
    {
        // Impersonasi lewat UserController::loginAs (key: admin_user_impersonating)
        $admin = $this->makeAdmin();
        $reviewer = $this->makeReviewer();

        $this->actingAs($admin)->post(route('admin.users.login-as', $reviewer));
        $this->assertSame($reviewer->id, Auth::id(), 'Setelah login-as, guard web harus jadi reviewer');

        $response = $this->post(route('impersonation.return'));

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionMissing('errors');
        $this->assertSame($admin->id, Auth::id(), 'Harus kembali ke admin asli');
        $this->assertNull(session('admin_user_impersonating'));
    }

    public function test_return_works_after_reviewer_controller_login_as(): void
    {
        // Impersonasi lewat ReviewerController::loginAs (key: admin_impersonating)
        $admin = $this->makeAdmin();
        $reviewer = $this->makeReviewer();

        $this->actingAs($admin)->post(route('admin.reviewers.login-as', $reviewer));
        $this->assertSame($reviewer->id, Auth::id());
        $this->assertSame($admin->id, session('admin_impersonating'));

        $response = $this->post(route('impersonation.return'));

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertSame($admin->id, Auth::id());
        $this->assertNull(session('admin_impersonating'));
    }

    public function test_return_button_is_shown_on_reviewer_page_while_impersonating(): void
    {
        $admin = $this->makeAdmin();
        $reviewer = $this->makeReviewer();

        $this->actingAs($admin)->post(route('admin.users.login-as', $reviewer));

        $response = $this->get(route('reviewer.dashboard'));

        $response->assertOk();
        $response->assertSee('Kembali ke Admin');
        $response->assertSee(route('impersonation.return'), false);
        $response->assertDontSee('admin/users/return-to-admin');
    }

    public function test_return_with_no_impersonation_session_redirects_to_login(): void
    {
        $reviewer = $this->makeReviewer();
        $this->actingAs($reviewer);

        $response = $this->post(route('impersonation.return'));

        $response->assertRedirect(route('login'));
        // Tetap login sebagai reviewer (tidak crash, tidak logout paksa).
        $this->assertSame($reviewer->id, Auth::id());
    }

    public function test_return_with_stale_non_admin_id_logs_out(): void
    {
        $reviewer = $this->makeReviewer();
        $someUser = $this->makeReviewer();

        // Simulasikan session impersonasi yang menunjuk ke ID non-admin.
        $this->actingAs($reviewer)->withSession(['admin_user_impersonating' => $someUser->id]);

        $response = $this->post(route('impersonation.return'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
        $this->assertFalse(Auth::check());
    }

    public function test_both_new_and_legacy_route_names_exist(): void
    {
        // Rute bersih yang baru.
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('impersonation.return'));
        // Alias kompatibilitas (path & nama lama) dipertahankan supaya tab
        // browser / view ter-cache yang belum ke-refresh tidak error 405.
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('admin.users.return-to-admin'));
    }

    /**
     * Regresi lanjutan 10 Sept: setelah rute POST lama dihapus total, path
     * /admin/users/return-to-admin jatuh ke Route::resource('users') dan
     * mengembalikan 405 (POST tidak didukung) untuk tab browser lama yang
     * masih submit ke sana. Alias kompatibilitas harus menerima POST di path
     * itu TANPA 405 & TANPA 403 (tidak digated AdminMiddleware).
     */
    public function test_legacy_path_still_accepts_post_without_405_or_403(): void
    {
        $admin = $this->makeAdmin();
        $reviewer = $this->makeReviewer();

        $this->actingAs($admin)->post(route('admin.users.login-as', $reviewer));
        $this->assertSame($reviewer->id, Auth::id());

        // POST ke path LAMA persis (seperti yang dilakukan tombol di view lama).
        $response = $this->post('/admin/users/return-to-admin');

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertSame($admin->id, Auth::id());
    }

    /**
     * Regresi 10 Sept 2026: saat admin sedang "Login As" reviewer, halaman admin
     * (mis. /admin/settings) memberi 403 — admin terkunci dari panel-nya sendiri.
     * AdminMiddleware sekarang mengizinkan akses kalau ada sesi impersonasi yang
     * ID admin-nya valid, walau user aktif (guard web) adalah reviewer.
     */
    public function test_admin_pages_stay_accessible_while_impersonating_via_user_login_as(): void
    {
        $admin = $this->makeAdmin();
        $reviewer = $this->makeReviewer();
        $this->actingAs($admin)->post(route('admin.users.login-as', $reviewer));
        $this->assertSame($reviewer->id, Auth::id());

        $this->get(route('admin.settings.index'))->assertOk();
        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_admin_pages_stay_accessible_while_impersonating_via_reviewer_login_as(): void
    {
        $admin = $this->makeAdmin();
        $reviewer = $this->makeReviewer();
        $this->actingAs($admin)->post(route('admin.reviewers.login-as', $reviewer));
        $this->assertSame($reviewer->id, Auth::id());

        $this->get(route('admin.settings.index'))->assertOk();
    }

    public function test_plain_reviewer_without_impersonation_session_is_still_403_on_admin_pages(): void
    {
        $this->actingAs($this->makeReviewer());

        $this->get(route('admin.settings.index'))->assertForbidden();
    }

    public function test_impersonation_session_pointing_to_a_non_admin_does_not_unlock_admin_pages(): void
    {
        $reviewer = $this->makeReviewer();
        $anotherReviewer = $this->makeReviewer();
        // Sesi impersonasi yang ID-nya menunjuk ke user NON-admin → tetap 403.
        $this->actingAs($reviewer)->withSession(['admin_user_impersonating' => $anotherReviewer->id]);

        $this->get(route('admin.settings.index'))->assertForbidden();
    }
}
