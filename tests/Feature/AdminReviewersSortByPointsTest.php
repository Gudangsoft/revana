<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Permintaan 10 Sept 2026: halaman /admin/reviewers (Daftar Reviewer) diurutkan
 * dari total point tertinggi (sebelumnya `->latest()` = terbaru dibuat dulu).
 * Diurutkan pakai kolom `total_points` — kolom yang sama yang ditampilkan di
 * tabel — dengan nama sebagai pemecah seri.
 */
class AdminReviewersSortByPointsTest extends TestCase
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

    private function makeReviewer(string $name, int $totalPoints, ?string $createdAt = null): User
    {
        $reviewer = User::create([
            'name' => $name,
            'email' => 'rev-' . uniqid() . '@example.test',
            'password' => bcrypt('password'),
            'role' => 'reviewer',
            'total_points' => $totalPoints,
            'available_points' => $totalPoints,
            'completed_reviews' => 0,
        ]);

        if ($createdAt) {
            // Bypass model timestamps untuk mengetes bahwa urutan TIDAK lagi
            // berdasarkan created_at.
            $reviewer->forceFill(['created_at' => $createdAt])->saveQuietly();
        }

        return $reviewer;
    }

    public function test_reviewers_are_listed_from_highest_total_points(): void
    {
        $this->actingAsAdmin();
        // Sengaja: yang poin tertinggi dibuat PALING AWAL, supaya kalau masih
        // pakai ->latest() dia malah muncul terakhir.
        $this->makeReviewer('Reviewer Tinggi', 500, '2020-01-01 00:00:00');
        $this->makeReviewer('Reviewer Sedang', 250, '2024-01-01 00:00:00');
        $this->makeReviewer('Reviewer Rendah', 10, now()->toDateTimeString());

        $response = $this->get(route('admin.reviewers.index'));

        $response->assertOk();
        $body = $response->getContent();

        $posTinggi = strpos($body, 'Reviewer Tinggi');
        $posSedang = strpos($body, 'Reviewer Sedang');
        $posRendah = strpos($body, 'Reviewer Rendah');

        $this->assertNotFalse($posTinggi);
        $this->assertLessThan($posSedang, $posTinggi, 'Reviewer 500 poin harus di atas reviewer 250 poin');
        $this->assertLessThan($posRendah, $posSedang, 'Reviewer 250 poin harus di atas reviewer 10 poin');
    }

    public function test_ties_on_points_are_broken_alphabetically_by_name(): void
    {
        $this->actingAsAdmin();
        $this->makeReviewer('Zulkifli', 100);
        $this->makeReviewer('Ahmad', 100);
        $this->makeReviewer('Mira', 100);

        $response = $this->get(route('admin.reviewers.index'));

        $response->assertOk();
        $body = $response->getContent();

        $this->assertLessThan(strpos($body, 'Mira'), strpos($body, 'Ahmad'));
        $this->assertLessThan(strpos($body, 'Zulkifli'), strpos($body, 'Mira'));
    }

    public function test_search_filter_still_works_with_new_ordering(): void
    {
        $this->actingAsAdmin();
        $this->makeReviewer('Budi Santoso', 300);
        $this->makeReviewer('Citra Dewi', 900);

        $response = $this->get(route('admin.reviewers.index', ['search' => 'Budi']));

        $response->assertOk();
        $response->assertSee('Budi Santoso');
        $response->assertDontSee('Citra Dewi');
    }

    public function test_export_collection_is_also_ordered_by_highest_total_points(): void
    {
        $this->actingAsAdmin();
        $this->makeReviewer('Reviewer Tinggi', 500, '2020-01-01 00:00:00');
        $this->makeReviewer('Reviewer Sedang', 250, now()->toDateTimeString());
        $this->makeReviewer('Reviewer Rendah', 5, now()->toDateTimeString());

        // XLSX = biner ter-zip, tidak bisa dicek urutannya via strpos di
        // response mentah — cek langsung koleksi yang diekspor.
        $rows = (new \App\Exports\ReviewersExport(null))->collection();

        $this->assertSame(
            ['Reviewer Tinggi', 'Reviewer Sedang', 'Reviewer Rendah'],
            $rows->pluck('name')->all()
        );
    }
}
