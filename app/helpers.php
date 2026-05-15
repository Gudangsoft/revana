<?php

use App\Models\Tenant;

if (!function_exists('tenant')) {
    /**
     * Ambil tenant aktif saat ini (null jika di portal master).
     */
    function tenant(): ?Tenant
    {
        return app()->bound('tenant') ? app('tenant') : null;
    }
}

if (!function_exists('tenant_has_feature')) {
    /**
     * Cek apakah tenant aktif memiliki fitur tertentu.
     * Di portal master selalu return true (tidak ada pembatasan).
     */
    function tenant_has_feature(string $feature): bool
    {
        $tenant = tenant();
        if (!$tenant) return true;
        return $tenant->hasFeature($feature);
    }
}
