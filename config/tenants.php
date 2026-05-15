<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Domain utama (super admin) — tidak diperlakukan sebagai tenant
    |--------------------------------------------------------------------------
    */
    'master_domain' => env('TENANT_MASTER_DOMAIN', 'portal.apji.org'),

    /*
    |--------------------------------------------------------------------------
    | Daftar fitur yang bisa di-toggle per tenant
    | key        → nama fitur (dipakai di routes, views, dan JSON features)
    | label      → label yang tampil di panel super admin
    | default    → nilai default saat tenant baru dibuat
    | group      → pengelompokan di UI
    |--------------------------------------------------------------------------
    */
    'features' => [
        'sms_gateway' => [
            'label'   => 'SMS Gateway / WhatsApp',
            'default' => true,
            'group'   => 'Komunikasi',
        ],
        'laporan_harian' => [
            'label'   => 'Laporan Harian PIC',
            'default' => true,
            'group'   => 'SDM',
        ],
        'marketing' => [
            'label'   => 'Modul Marketing',
            'default' => false,
            'group'   => 'SDM',
        ],
        'reviewer' => [
            'label'   => 'Modul Reviewer',
            'default' => true,
            'group'   => 'Review',
        ],
        'fasttrack' => [
            'label'   => 'Fasttrack Submission',
            'default' => false,
            'group'   => 'Submission',
        ],
        'bkd' => [
            'label'   => 'Laporan BKD',
            'default' => false,
            'group'   => 'Submission',
        ],
        'jafa' => [
            'label'   => 'JAFA Journal',
            'default' => false,
            'group'   => 'Submission',
        ],
        'export_csv' => [
            'label'   => 'Export CSV',
            'default' => true,
            'group'   => 'Utilitas',
        ],
        'loa_verify' => [
            'label'   => 'Verifikasi LoA Publik',
            'default' => true,
            'group'   => 'Utilitas',
        ],
        'multi_journal' => [
            'label'   => 'Multi Jurnal',
            'default' => true,
            'group'   => 'Jurnal',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Paket / plan yang tersedia
    |--------------------------------------------------------------------------
    */
    'plans' => [
        'trial' => [
            'label'    => 'Trial',
            'duration' => 14, // hari
            'features' => ['sms_gateway', 'laporan_harian', 'reviewer', 'export_csv', 'loa_verify', 'multi_journal'],
        ],
        'basic' => [
            'label'    => 'Basic',
            'duration' => 365,
            'features' => ['sms_gateway', 'laporan_harian', 'reviewer', 'export_csv', 'loa_verify', 'multi_journal'],
        ],
        'pro' => [
            'label'    => 'Pro',
            'duration' => 365,
            'features' => ['sms_gateway', 'laporan_harian', 'marketing', 'reviewer', 'fasttrack', 'export_csv', 'loa_verify', 'multi_journal'],
        ],
        'enterprise' => [
            'label'    => 'Enterprise',
            'duration' => 365,
            'features' => ['sms_gateway', 'laporan_harian', 'marketing', 'reviewer', 'fasttrack', 'bkd', 'jafa', 'export_csv', 'loa_verify', 'multi_journal'],
        ],
    ],

];
