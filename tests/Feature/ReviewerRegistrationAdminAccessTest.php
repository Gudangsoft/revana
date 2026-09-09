<?php

namespace Tests\Feature;

use App\Models\FieldOfStudy;
use App\Models\ReviewerRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresi 9 Sept 2026: user melapor "reviewer baru saat register datane belum
 * masuk". Diselidiki: pendaftaran via form publik (/daftar-reviewer) TERSIMPAN
 * dengan benar ke tabel `reviewer_registrations` (store() sudah benar), tapi
 * SEMUA rute admin untuk melihat/approve/reject pendaftaran itu
 * (admin.reviewer-registrations.*) TIDAK PERNAH didaftarkan di routes/web.php
 * sama sekali — padahal controller & view-nya sudah lengkap sejak lama, dan
 * dipanggil lewat route() di view itu sendiri. Akibatnya klik apa pun ke
 * halaman itu akan menghasilkan RouteNotFoundException, dan memang tidak ada
 * link/menu sidebar ke sana — admin tidak pernah bisa melihat data yang
 * sebenarnya sudah masuk. Diperbaiki dengan mendaftarkan 6 rute yang hilang
 * dan menambah menu sidebar "Pendaftaran Reviewer" (dengan badge jumlah
 * pending, mengikuti pola badge lain di ViewServiceProvider).
 */
class ReviewerRegistrationAdminAccessTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $admin = User::create([
            'name' => 'Test Admin', 'email' => 'admin-' . uniqid() . '@example.test',
            'password' => bcrypt('password'), 'role' => 'admin',
        ]);
        $this->actingAs($admin);

        return $admin;
    }

    private function makeField(): FieldOfStudy
    {
        return FieldOfStudy::create(['name' => 'Bidang ' . uniqid(), 'is_active' => true, 'order' => 1]);
    }

    private function makePendingRegistration(array $overrides = []): ReviewerRegistration
    {
        $field = $this->makeField();

        return ReviewerRegistration::create(array_merge([
            'full_name' => 'Dr. Reviewer Baru',
            'email' => 'reviewer-' . uniqid() . '@example.test',
            'affiliation' => 'Universitas Test',
            'whatsapp' => '081234567890',
            'password' => bcrypt('password123'),
            'field_of_study' => $field->name,
            'field_of_study_id' => $field->id,
            'sinta_id' => 'SINTA123',
            'article_languages' => ['Indonesia'],
            'status' => 'pending',
        ], $overrides));
    }

    /**
     * Sebelum perbaikan, semua route() ke admin.reviewer-registrations.* akan
     * melempar RouteNotFoundException — jadi assertion paling dasar dulu:
     * route-nya harus benar-benar terdaftar & bisa di-generate.
     */
    public function test_all_admin_reviewer_registration_routes_are_registered(): void
    {
        $registration = $this->makePendingRegistration();

        $this->assertNotEmpty(route('admin.reviewer-registrations.index'));
        $this->assertNotEmpty(route('admin.reviewer-registrations.show', $registration));
        $this->assertNotEmpty(route('admin.reviewer-registrations.approve', $registration));
        $this->assertNotEmpty(route('admin.reviewer-registrations.reject', $registration));
        $this->assertNotEmpty(route('admin.reviewer-registrations.destroy', $registration));
        $this->assertNotEmpty(route('admin.reviewer-registrations.bulk-approve'));
    }

    public function test_admin_can_view_pending_registrations_index(): void
    {
        $this->actingAsAdmin();
        $registration = $this->makePendingRegistration(['full_name' => 'Dr. Budi Santoso']);

        $response = $this->get(route('admin.reviewer-registrations.index'));

        $response->assertOk();
        $response->assertSee('Dr. Budi Santoso');
    }

    public function test_admin_can_view_registration_detail(): void
    {
        $this->actingAsAdmin();
        $registration = $this->makePendingRegistration(['full_name' => 'Dr. Siti Aminah']);

        $response = $this->get(route('admin.reviewer-registrations.show', $registration));

        $response->assertOk();
        $response->assertSee('Dr. Siti Aminah');
    }

    public function test_admin_can_approve_registration_and_user_account_is_created(): void
    {
        $this->actingAsAdmin();
        $registration = $this->makePendingRegistration([
            'full_name' => 'Dr. Approved Reviewer',
            'email' => 'approved-reviewer@example.test',
        ]);

        $response = $this->post(route('admin.reviewer-registrations.approve', $registration));

        $response->assertRedirect(route('admin.reviewer-registrations.index'));
        $this->assertDatabaseHas('reviewer_registrations', [
            'id' => $registration->id,
            'status' => 'approved',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'approved-reviewer@example.test',
            'role' => 'reviewer',
        ]);
    }

    public function test_admin_can_reject_registration(): void
    {
        $this->actingAsAdmin();
        $registration = $this->makePendingRegistration();

        $response = $this->post(route('admin.reviewer-registrations.reject', $registration), [
            'notes' => 'Data SINTA tidak valid',
        ]);

        $response->assertRedirect(route('admin.reviewer-registrations.index'));
        $this->assertDatabaseHas('reviewer_registrations', [
            'id' => $registration->id,
            'status' => 'rejected',
        ]);
    }

    public function test_admin_can_bulk_approve_multiple_registrations(): void
    {
        $this->actingAsAdmin();
        $reg1 = $this->makePendingRegistration(['email' => 'bulk1@example.test']);
        $reg2 = $this->makePendingRegistration(['email' => 'bulk2@example.test']);

        $response = $this->post(route('admin.reviewer-registrations.bulk-approve'), [
            'registration_ids' => [$reg1->id, $reg2->id],
        ]);

        $response->assertRedirect(route('admin.reviewer-registrations.index'));
        $this->assertDatabaseHas('users', ['email' => 'bulk1@example.test', 'role' => 'reviewer']);
        $this->assertDatabaseHas('users', ['email' => 'bulk2@example.test', 'role' => 'reviewer']);
    }

    public function test_admin_can_delete_a_registration(): void
    {
        $this->actingAsAdmin();
        $registration = $this->makePendingRegistration();

        $response = $this->delete(route('admin.reviewer-registrations.destroy', $registration));

        $response->assertRedirect(route('admin.reviewer-registrations.index'));
        $this->assertDatabaseMissing('reviewer_registrations', ['id' => $registration->id]);
    }

    /**
     * Sidebar admin sebelumnya TIDAK PUNYA menu sama sekali ke halaman ini —
     * pastikan sekarang muncul, dengan badge jumlah pending yang benar.
     */
    public function test_admin_sidebar_shows_pending_registration_badge(): void
    {
        $this->actingAsAdmin();
        $this->makePendingRegistration();
        $this->makePendingRegistration();

        // Halaman admin mana pun cukup untuk merender sidebar (composer 'admin.*').
        $response = $this->get(route('admin.reviewer-registrations.index'));

        $response->assertOk();
        $response->assertSee('Pendaftaran Reviewer');
        $response->assertSee(route('admin.reviewer-registrations.index', absolute: false), false);
    }

    /**
     * Uji end-to-end: submit lewat form publik /daftar-reviewer, lalu pastikan
     * admin BENAR-BENAR bisa melihatnya di halaman admin (menyambungkan kedua
     * sisi bug yang dilaporkan user).
     */
    public function test_public_registration_is_visible_to_admin_afterwards(): void
    {
        $field = $this->makeField();

        $this->withSession(['captcha_answer' => 15])->post(route('reviewer-registration.store'), [
            'full_name' => 'Dr. End To End',
            'email' => 'endtoend@example.test',
            'affiliation' => 'Universitas Test',
            'whatsapp' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'field_of_study_id' => $field->id,
            'sinta_id' => 'SINTA-E2E',
            'article_languages' => ['Indonesia'],
            'captcha' => 15,
        ]);

        $this->assertDatabaseHas('reviewer_registrations', [
            'email' => 'endtoend@example.test',
            'status' => 'pending',
        ]);

        $this->actingAsAdmin();
        $response = $this->get(route('admin.reviewer-registrations.index'));

        $response->assertOk();
        $response->assertSee('Dr. End To End');
    }
}
