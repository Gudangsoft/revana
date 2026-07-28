<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Cache leaderboard poin PIC/Marketing dipakai di 2 tempat dengan KEY BERBEDA:
 * - Widget dashboard admin (DashboardController::dashboard()) pakai key BER-TENANT:
 *   "rankings.topPics.{tenantKey}" / "rankings.topMarketings.{tenantKey}" (TTL 5 menit).
 * - Beberapa tempat lain sempat cuma forget() key TANPA tenant ("rankings.topPics"
 *   polos) — tidak pernah benar-benar menghapus cache yang dipakai widget dashboard,
 *   jadi widget itu bisa menampilkan data lama sampai 5 menit walau total_points
 *   sudah benar di database. Helper ini menghapus KEDUA variasi sekaligus supaya
 *   tidak terulang lagi di tempat baru manapun yang perlu invalidasi cache ranking.
 */
class RankingCache
{
    public static function forgetPics(): void
    {
        Cache::forget('rankings.topPics');
        Cache::forget('rankings.topPics.' . self::tenantKey());
    }

    public static function forgetMarketings(): void
    {
        Cache::forget('rankings.topMarketings');
        Cache::forget('rankings.topMarketings.' . self::tenantKey());
    }

    private static function tenantKey(): string
    {
        return app()->bound('tenant') ? app('tenant')->subdomain : 'master';
    }
}
