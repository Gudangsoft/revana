<?php

namespace Tests\Feature\Points;

use App\Support\RankingCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Regresi: widget "Peringkat Point PIC/Marketing" di dashboard admin
 * (DashboardController::dashboard()) memakai cache KEY BER-TENANT
 * ("rankings.topPics.{tenantKey}", TTL 5 menit) — tapi banyak tempat di kode cuma
 * memanggil Cache::forget('rankings.topPics') TANPA tenant key, jadi cache dashboard
 * itu TIDAK PERNAH benar-benar ter-invalidate setelah poin baru diberikan atau
 * disinkronkan. Helper RankingCache memastikan KEDUA variasi key selalu dihapus
 * bersamaan, di semua tempat yang butuh invalidasi cache ranking.
 */
class RankingCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_forget_pics_clears_both_plain_and_tenant_scoped_keys(): void
    {
        Cache::put('rankings.topPics', 'data-lama', 300);
        Cache::put('rankings.topPics.master', 'data-lama-tenant', 300);

        RankingCache::forgetPics();

        $this->assertNull(Cache::get('rankings.topPics'));
        $this->assertNull(Cache::get('rankings.topPics.master'));
    }

    public function test_forget_marketings_clears_both_plain_and_tenant_scoped_keys(): void
    {
        Cache::put('rankings.topMarketings', 'data-lama', 300);
        Cache::put('rankings.topMarketings.master', 'data-lama-tenant', 300);

        RankingCache::forgetMarketings();

        $this->assertNull(Cache::get('rankings.topMarketings'));
        $this->assertNull(Cache::get('rankings.topMarketings.master'));
    }
}
