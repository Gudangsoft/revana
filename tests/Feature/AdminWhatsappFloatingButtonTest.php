<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Fitur 10 Sept 2026: tombol WhatsApp melayang di /reviewer/dashboard untuk
 * menghubungi admin, dengan nomor diatur di /admin/settings ("Nomor WhatsApp
 * Admin", key Setting `admin_whatsapp`).
 */
class AdminWhatsappFloatingButtonTest extends TestCase
{
    use RefreshDatabase;

    private string $envBackup = '';

    protected function setUp(): void
    {
        parent::setUp();
        // SettingController::update() menulis ulang base_path('.env') — snapshot
        // & pulihkan supaya test tidak mengubah file .env asli.
        $path = base_path('.env');
        $this->envBackup = File::exists($path) ? File::get($path) : '';
    }

    protected function tearDown(): void
    {
        if ($this->envBackup !== '') {
            File::put(base_path('.env'), $this->envBackup);
        }
        parent::tearDown();
    }

    private function actingAsAdmin(): User
    {
        $admin = User::create([
            'name' => 'Test Admin', 'email' => 'admin-' . uniqid() . '@example.test',
            'password' => bcrypt('password'), 'role' => 'admin',
        ]);
        $this->actingAs($admin);

        return $admin;
    }

    private function makeReviewer(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Reviewer Test', 'email' => 'rev-' . uniqid() . '@example.test',
            'password' => bcrypt('password'), 'role' => 'reviewer',
            'total_points' => 0, 'available_points' => 0, 'completed_reviews' => 0,
        ], $overrides));
    }

    private function submitSettings(array $overrides = []): \Illuminate\Testing\TestResponse
    {
        return $this->put(route('admin.settings.update'), array_merge([
            // Nilai wajib — samakan dengan .env supaya penulisan ulang jadi no-op.
            'app_name' => config('app.name'),
            'app_url'  => config('app.url'),
        ], $overrides));
    }

    public function test_admin_settings_page_shows_admin_whatsapp_field(): void
    {
        Setting::set('admin_whatsapp', '08123456789');
        Cache::flush();
        $this->actingAsAdmin();

        $response = $this->get(route('admin.settings.index'));

        $response->assertOk();
        $response->assertSee('Nomor WhatsApp Admin');
        $response->assertSee('name="admin_whatsapp"', false);
        $response->assertSee('08123456789', false);
    }

    public function test_admin_can_save_admin_whatsapp_number(): void
    {
        $this->actingAsAdmin();

        $response = $this->submitSettings(['admin_whatsapp' => '  0812-3456-789  ']);

        $response->assertRedirect(route('admin.settings.index'));
        // Disimpan apa adanya (di-trim); normalisasi wa.me terjadi saat render.
        $this->assertSame('0812-3456-789', Setting::get('admin_whatsapp'));
    }

    public function test_reviewer_dashboard_shows_floating_button_with_normalised_link(): void
    {
        Setting::set('admin_whatsapp', '08123456789');
        Cache::flush();
        $this->actingAs($this->makeReviewer());

        $response = $this->get(route('reviewer.dashboard'));

        $response->assertOk();
        $response->assertSee('reviewer-wa-float', false);
        // 08... harus dinormalisasi ke 62...
        $response->assertSee('https://wa.me/628123456789?text=', false);
        $response->assertDontSee('wa.me/08123456789', false);
    }

    public function test_link_normalisation_keeps_existing_country_code_and_strips_symbols(): void
    {
        Setting::set('admin_whatsapp', '+62 812-3456-000');
        Cache::flush();
        $this->actingAs($this->makeReviewer());

        $response = $this->get(route('reviewer.dashboard'));

        $response->assertOk();
        $response->assertSee('https://wa.me/628123456000?text=', false);
    }

    public function test_no_floating_button_when_admin_whatsapp_is_empty(): void
    {
        Setting::set('admin_whatsapp', '');
        Cache::flush();
        $this->actingAs($this->makeReviewer());

        $response = $this->get(route('reviewer.dashboard'));

        $response->assertOk();
        $response->assertDontSee('reviewer-wa-float', false);
    }
}
