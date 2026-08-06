<?php

namespace Tests\Feature;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fitur 1 Agustus 2026: trigger `notify_penulis` (email ke author saat submission
 * baru dibuat) sudah lama dipakai kode (SubmissionController::sendPenulisEmail())
 * tapi tidak pernah muncul di UI Template Email Monitoring — grid dan dropdown
 * cuma menyaring trigger berawalan assign_/validate_. User melaporkan "template
 * email ke author belum ada". Diperbaiki dengan menambah kelompok "Ke Penulis
 * (Author)" di kedua tempat, plus migration yang men-seed template default
 * (non-aktif sampai admin meninjau redaksinya).
 */
class EmailTemplateNotifyPenulisTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $admin = User::create([
            'name' => 'Test Admin',
            'email' => 'admin-' . uniqid() . '@example.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $this->actingAs($admin);

        return $admin;
    }

    public function test_migration_seeds_inactive_notify_penulis_template(): void
    {
        $tpl = EmailTemplate::where('trigger_key', 'notify_penulis')->first();

        $this->assertNotNull($tpl, 'Migration seed harus membuat template notify_penulis');
        $this->assertFalse($tpl->is_active, 'Template hasil seed harus non-aktif sampai admin meninjau');
    }

    public function test_seeded_template_renders_all_declared_variables(): void
    {
        $tpl = EmailTemplate::where('trigger_key', 'notify_penulis')->firstOrFail();

        $rendered = $tpl->render([
            'nama_artikel' => 'Judul Contoh',
            'kode_submit' => 'SUB001',
            'id_artikel' => 'ART001',
            'nama_jurnal' => 'Jurnal Contoh',
            'url_jurnal' => 'https://example.test',
            'nama_penulis' => 'Budi',
            'username_author' => 'budi123',
            'password_author' => 'rahasia',
            'app_name' => 'SIPERA',
            'tanggal' => '01/08/2026 10:00',
        ]);

        $this->assertStringContainsString('Judul Contoh', $rendered['subject']);
        $this->assertStringContainsString('Budi', $rendered['body']);
        $this->assertStringContainsString('budi123', $rendered['body']);
        $this->assertStringNotContainsString('{nama_penulis}', $rendered['body']);
    }

    public function test_index_page_shows_notify_group_with_badge_when_template_exists(): void
    {
        $this->actingAsAdmin();

        $response = $this->get(route('admin.email-templates.index'));

        $response->assertOk();
        $response->assertSee('Ke Penulis (Author)');
        $response->assertSee('Notifikasi ke Penulis (saat submission masuk)');
    }

    public function test_index_page_shows_create_link_when_notify_template_missing(): void
    {
        $this->actingAsAdmin();
        EmailTemplate::where('trigger_key', 'notify_penulis')->delete();

        $response = $this->get(route('admin.email-templates.index'));

        $response->assertOk();
        $response->assertSee(route('admin.email-templates.create', ['trigger_key' => 'notify_penulis']), false);
    }

    public function test_create_page_dropdown_offers_notify_penulis_when_available(): void
    {
        $this->actingAsAdmin();
        EmailTemplate::where('trigger_key', 'notify_penulis')->delete();

        $response = $this->get(route('admin.email-templates.create'));

        $response->assertOk();
        $response->assertSee('Ke Penulis (Author)');
        $response->assertSee('<option value="notify_penulis"', false);
    }

    public function test_can_actually_create_notify_penulis_template_via_store(): void
    {
        $this->actingAsAdmin();
        EmailTemplate::where('trigger_key', 'notify_penulis')->delete();

        $response = $this->post(route('admin.email-templates.store'), [
            'name' => 'Notifikasi Penulis Custom',
            'trigger_key' => 'notify_penulis',
            'subject' => 'Halo {nama_penulis}',
            'body' => 'Artikel {nama_artikel} diterima.',
            'is_active' => '1',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('email_templates', [
            'trigger_key' => 'notify_penulis',
            'name' => 'Notifikasi Penulis Custom',
        ]);
    }
}
