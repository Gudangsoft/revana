<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Referensi Jurnal — {{ $settings['app_name'] }}</title>
    @if($settings['favicon'])
    <link rel="icon" href="{{ asset('storage/' . $settings['favicon']) }}" type="image/x-icon">
    @endif
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
    :root {
        --primary: #4f46e5;
        --primary2: #7c3aed;
        --gold: #f59e0b;
        --topbar-h: 58px;
        --sidebar-w: 280px;
    }
    *, *::before, *::after { box-sizing: border-box; }
    html { scroll-behavior: smooth; }
    body {
        margin: 0; padding: 0;
        background: #f1f5f9;
        font-family: 'Segoe UI', system-ui, sans-serif;
        min-height: 100vh;
    }

    /* ══ SCROLL PROGRESS BAR ══ */
    #scroll-progress {
        position: fixed; top: 0; left: 0; height: 3px; z-index: 9999;
        background: linear-gradient(90deg, #818cf8, #c084fc);
        width: 0%; transition: width .1s linear;
    }

    /* ══ TOPBAR ══ */
    .topbar {
        position: sticky; top: 0; z-index: 500;
        height: var(--topbar-h);
        display: flex; align-items: center; padding: 0 28px;
        background: linear-gradient(135deg, var(--primary), var(--primary2));
        transition: box-shadow .25s, background .25s;
    }
    .topbar.scrolled {
        box-shadow: 0 4px 20px rgba(79,70,229,.4);
        background: rgba(79,70,229,.97);
        backdrop-filter: blur(10px);
    }
    .topbar-brand { display: flex; align-items: center; gap: 10px; }
    .topbar-brand img  { height: 32px; }
    .topbar-brand span { color: #fff; font-weight: 700; font-size: 1rem; }
    .topbar-login {
        margin-left: auto;
        color: rgba(255,255,255,.88); font-size: .82rem; text-decoration: none;
        display: flex; align-items: center; gap: 6px;
        border: 1px solid rgba(255,255,255,.3); border-radius: 8px;
        padding: 5px 14px; transition: background .15s, color .15s;
    }
    .topbar-login:hover { background: rgba(255,255,255,.18); color: #fff; }

    /* ══ HERO ══ */
    .hero {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary2) 55%, #6d28d9 100%);
        padding: 44px 28px 48px;
        position: relative; overflow: hidden;
    }
    .hero::before {
        content: ''; position: absolute;
        top: -80px; right: -80px; width: 340px; height: 340px;
        background: rgba(255,255,255,.07); border-radius: 50%;
    }
    .hero::after {
        content: ''; position: absolute;
        bottom: -60px; left: 8%; width: 220px; height: 220px;
        background: rgba(255,255,255,.05); border-radius: 50%;
    }
    .hero-inner {
        position: relative; z-index: 1;
        max-width: 1360px; margin: 0 auto;
        text-align: center;
    }
    .hero h1 {
        color: #fff; font-size: 2.2rem; font-weight: 900;
        letter-spacing: -.02em; margin-bottom: 6px; line-height: 1.2;
        text-transform: uppercase;
    }
    .hero-sub { color: rgba(255,255,255,.75); font-size: .9rem; margin-bottom: 18px; }
    .stat-pill {
        display: inline-flex; align-items: center; gap: 6px;
        background: rgba(255,255,255,.13);
        border: 1px solid rgba(255,255,255,.22);
        backdrop-filter: blur(6px);
        border-radius: 999px; padding: 5px 14px;
        font-size: .78rem; font-weight: 600; color: #fff;
    }

    /* ══ LAYOUT WRAPPER ══ */
    .layout {
        max-width: 1360px; margin: 0 auto;
        padding: 28px 20px 60px;
        display: grid;
        grid-template-columns: var(--sidebar-w) 1fr;
        gap: 22px;
        align-items: start;
    }

    /* ══ SIDEBAR ══ */
    .filter-sidebar {
        position: sticky;
        top: calc(var(--topbar-h) + 12px);
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 16px rgba(0,0,0,.08);
        overflow: hidden;
        transition: box-shadow .2s;
    }
    .filter-sidebar:hover { box-shadow: 0 6px 28px rgba(99,102,241,.14); }
    .filter-sidebar-head {
        background: linear-gradient(135deg, var(--primary), var(--primary2));
        padding: 13px 18px;
        color: #fff; font-weight: 700; font-size: .88rem;
        display: flex; align-items: center; gap: 8px;
    }
    .filter-sidebar-body { padding: 18px 16px 16px; }
    .filter-group { margin-bottom: 15px; }
    .filter-group label {
        display: flex; align-items: center; gap: 5px;
        font-size: .72rem; font-weight: 700;
        color: #6b7280; text-transform: uppercase;
        letter-spacing: .06em; margin-bottom: 6px;
    }
    .filter-group .form-control,
    .filter-group .form-select {
        border-radius: 9px; border: 1px solid #e5e7eb;
        font-size: .84rem; padding: 8px 11px;
        transition: border-color .15s, box-shadow .15s;
        background: #fafafa;
    }
    .filter-group .form-control:focus,
    .filter-group .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(99,102,241,.12);
        background: #fff;
    }
    .btn-filter {
        width: 100%; border-radius: 10px; font-weight: 700;
        background: linear-gradient(135deg, var(--primary), var(--primary2));
        border: none; color: #fff; padding: 10px;
        font-size: .87rem; cursor: pointer;
        transition: opacity .15s, transform .1s;
        letter-spacing: .01em;
    }
    .btn-filter:hover  { opacity: .88; }
    .btn-filter:active { transform: scale(.98); }
    .btn-reset {
        width: 100%; border-radius: 10px; font-weight: 600;
        background: #f3f4f6; border: 1px solid #e5e7eb;
        color: #6b7280; padding: 8px; font-size: .81rem;
        text-decoration: none; display: block; text-align: center;
        margin-top: 8px; transition: background .15s;
    }
    .btn-reset:hover { background: #e5e7eb; color: #374151; }

    /* Filter active pills */
    .af-section { margin-top: 14px; padding-top: 12px; border-top: 1px dashed #e5e7eb; }
    .af-label { font-size: .68rem; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 6px; }
    .af-pill {
        display: inline-flex; align-items: center; gap: 4px;
        background: #ede9fe; color: #4338ca;
        border-radius: 999px; font-size: .7rem; font-weight: 600;
        padding: 2px 9px; margin: 2px 2px 2px 0;
    }
    .af-pill a { color: #6366f1; text-decoration: none; font-size: .8rem; line-height: 1; }
    .af-pill a:hover { color: #ef4444; }

    /* ══ MAIN COL ══ */
    .result-bar {
        background: #fff; border-radius: 10px;
        padding: 9px 14px; margin-bottom: 12px;
        display: flex; align-items: center; justify-content: space-between;
        box-shadow: 0 1px 4px rgba(0,0,0,.05);
        font-size: .82rem; color: #6b7280;
    }
    .result-bar strong { color: #111827; }

    /* ══ REF CARD ══ */
    .ref-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 1px 6px rgba(0,0,0,.07);
        margin-bottom: 14px;
        overflow: hidden;
        border: 1px solid #eef2ff;
        transition: box-shadow .2s, transform .2s;
        animation: fadeSlideUp .35s ease both;
    }
    .ref-card:nth-child(1) { animation-delay: .05s; }
    .ref-card:nth-child(2) { animation-delay: .10s; }
    .ref-card:nth-child(3) { animation-delay: .15s; }
    .ref-card:nth-child(4) { animation-delay: .20s; }
    .ref-card:nth-child(5) { animation-delay: .25s; }
    @keyframes fadeSlideUp {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .ref-card:hover {
        box-shadow: 0 8px 32px rgba(79,70,229,.14);
        transform: translateY(-2px);
    }
    .ref-card-strip { height: 4px; }
    .ref-card-body  { padding: 18px 22px; }

    /* Nomor */
    .ref-number {
        display: inline-flex; align-items: center; justify-content: center;
        width: 30px; height: 30px; border-radius: 9px;
        font-size: .76rem; font-weight: 800;
        flex-shrink: 0; margin-right: 11px; margin-top: 1px;
    }
    .ref-number-nas { background: #ede9fe; color: #5b21b6; }
    .ref-number-int { background: #fef3c7; color: #92400e; }
    .ref-number-oth { background: #f3f4f6; color: #6b7280; }

    /* Judul */
    .ref-title { font-size: .97rem; font-weight: 700; color: #111827; line-height: 1.4; }

    /* Badges */
    .badge-nas {
        background: linear-gradient(135deg, var(--primary), #818cf8);
        color: #fff; font-size: .69rem; padding: 3px 10px; border-radius: 8px; font-weight: 600;
    }
    .badge-int {
        background: linear-gradient(135deg, var(--gold), #fbbf24);
        color: #fff; font-size: .69rem; padding: 3px 10px; border-radius: 8px; font-weight: 600;
    }
    .badge-oth {
        background: #e5e7eb; color: #4b5563;
        font-size: .69rem; padding: 3px 10px; border-radius: 8px; font-weight: 600;
    }
    .badge-meta {
        background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0;
        font-size: .69rem; padding: 2px 8px; border-radius: 7px; font-weight: 500;
    }

    /* Divider */
    .ref-divider { border: none; border-top: 1px dashed #e5e7eb; margin: 12px 0; }

    /* Section label */
    .section-label {
        font-size: .69rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .06em;
        display: flex; align-items: center; gap: 5px; margin-bottom: 8px;
    }
    .label-ref { color: #4f46e5; }

    /* Blok Referensi */
    .ref-text-wrap {
        position: relative;
        background: linear-gradient(135deg, #f0f4ff, #f8f9ff);
        border: 1px solid #c7d2fe;
        border-left: 4px solid #6366f1;
        border-radius: 0 10px 10px 0;
        padding: 14px 16px 14px 20px;
        overflow: hidden;
    }
    .ref-text-wrap::before {
        content: '\201C';
        position: absolute; top: -6px; left: 10px;
        font-size: 3.2rem; line-height: 1; color: #c7d2fe;
        font-family: Georgia, serif; pointer-events: none;
    }
    .ref-text {
        font-size: .9rem; line-height: 1.8; color: #1e1b4b;
        font-family: Georgia, 'Times New Roman', serif;
        margin: 0; padding-left: 6px;
    }
    .ref-doi {
        color: #4f46e5; font-style: italic; text-decoration: none; font-size: .83rem;
        font-family: 'Segoe UI', sans-serif;
    }
    .ref-doi:hover { text-decoration: underline; }

    /* Copy button */
    .copy-btn {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 11px; font-size: .72rem; font-weight: 500;
        border-radius: 7px; border: 1px solid #d1d5db;
        background: #fff; color: #4b5563;
        cursor: pointer; transition: all .15s; flex-shrink: 0;
    }
    .copy-btn:hover  { background: #f3f4f6; border-color: #9ca3af; }
    .copy-btn.copied { background: #d1fae5; border-color: #34d399; color: #065f46; }

    /* ══ CITE PANEL ══ */
    .cite-panel {
        margin-top: 12px;
        border: 1px solid #e0e7ff;
        border-radius: 10px;
        overflow: hidden;
        transition: box-shadow .15s;
    }
    .cite-panel:hover { box-shadow: 0 2px 12px rgba(99,102,241,.1); }
    .cite-panel-head {
        background: linear-gradient(90deg, #f0f4ff, #f5f3ff);
        padding: 9px 14px;
        display: flex; align-items: center; gap: 8px;
        font-size: .72rem; font-weight: 700;
        color: #4338ca; text-transform: uppercase; letter-spacing: .05em;
        cursor: pointer; user-select: none;
        transition: background .15s;
    }
    .cite-panel-head:hover { background: #ede9fe; }
    .cite-count {
        font-size: .64rem; font-weight: 700;
        background: #c7d2fe; color: #3730a3;
        border-radius: 999px; padding: 1px 8px;
    }
    .cite-toggle {
        margin-left: auto; background: none; border: none;
        color: #6366f1; cursor: pointer; padding: 0;
        transition: transform .2s;
    }
    .cite-body  { padding: 12px 14px 14px; background: #fafaff; }
    .cite-tabs  {
        display: flex; flex-wrap: wrap; gap: 5px;
        margin-bottom: 10px; padding-bottom: 10px;
        border-bottom: 1px dashed #e0e7ff;
    }
    .cite-tab {
        padding: 4px 13px; font-size: .75rem; font-weight: 600;
        border-radius: 8px; border: 1px solid #c7d2fe;
        background: #fff; color: #4f46e5; cursor: pointer;
        transition: all .13s;
    }
    .cite-tab:hover  { background: #ede9fe; }
    .cite-tab.active {
        background: linear-gradient(135deg, var(--primary), var(--primary2));
        color: #fff; border-color: transparent;
        box-shadow: 0 2px 8px rgba(99,102,241,.3);
    }
    .kut-text-wrap {
        position: relative;
        background: linear-gradient(135deg, #fdf4ff, #faf5ff);
        border: 1px solid #e9d5ff;
        border-left: 4px solid #a78bfa;
        border-radius: 0 10px 10px 0;
        padding: 12px 14px 12px 20px;
        overflow: hidden;
    }
    .kut-text-wrap::before {
        content: '\201C';
        position: absolute; top: -6px; left: 10px;
        font-size: 3.2rem; line-height: 1; color: #ddd6fe;
        font-family: Georgia, serif; pointer-events: none;
    }
    .kut-text {
        font-size: .86rem; line-height: 1.75; color: #3b0764;
        font-family: 'Courier New', Courier, monospace;
        margin: 0; padding-left: 6px; word-break: break-word;
    }

    /* ══ PAGINATION ══ */
    .pagination { gap: 4px; }
    .page-link {
        border-radius: 9px !important; border: 1px solid #e5e7eb;
        color: var(--primary); font-size: .82rem; min-width: 36px;
        text-align: center; transition: all .15s;
    }
    .page-item.active .page-link {
        background: linear-gradient(135deg, var(--primary), var(--primary2));
        border-color: transparent; color: #fff;
        box-shadow: 0 3px 10px rgba(99,102,241,.35);
    }
    .page-link:hover { background: #ede9fe; border-color: #c7d2fe; }

    /* ══ EMPTY ══ */
    .empty-state {
        text-align: center; padding: 64px 20px; color: #9ca3af;
        background: #fff; border-radius: 14px;
        box-shadow: 0 1px 6px rgba(0,0,0,.06);
    }
    .empty-state i { font-size: 3.5rem; display: block; margin-bottom: 14px; opacity: .35; }

    /* ══ BACK TO TOP ══ */
    #back-top {
        position: fixed; bottom: 28px; right: 24px; z-index: 400;
        width: 42px; height: 42px; border-radius: 12px;
        background: linear-gradient(135deg, var(--primary), var(--primary2));
        color: #fff; border: none; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem;
        box-shadow: 0 4px 16px rgba(79,70,229,.4);
        opacity: 0; pointer-events: none;
        transition: opacity .25s, transform .25s;
        transform: translateY(12px);
    }
    #back-top.show { opacity: 1; pointer-events: all; transform: translateY(0); }
    #back-top:hover { opacity: .88; }

    /* ══ FOOTER ══ */
    .page-footer { text-align: center; font-size: .77rem; color: #94a3b8; padding: 20px 0 4px; }

    /* ══ RESPONSIVE ══ */
    @media (max-width: 900px) {
        .layout { grid-template-columns: 1fr; }
        .filter-sidebar { position: static; }
        .hero h1 { font-size: 1.5rem; }
        .hero { padding: 28px 18px 36px; }
    }
    </style>
</head>
<body>

{{-- Scroll progress bar --}}
<div id="scroll-progress"></div>

{{-- Back to top --}}
<button id="back-top" onclick="window.scrollTo({top:0,behavior:'smooth'})" title="Ke atas">
    <i class="bi bi-arrow-up"></i>
</button>

{{-- ══ TOPBAR ══ --}}
<nav class="topbar" id="topbar">
    <div class="topbar-brand">
        @if($settings['logo'])
            <img src="{{ asset('storage/' . $settings['logo']) }}" alt="Logo">
        @else
            <i class="bi bi-journal-check text-white fs-5"></i>
        @endif
        <span>{{ $settings['app_name'] }}</span>
    </div>
    <a href="{{ route('login') }}" class="topbar-login">
        <i class="bi bi-box-arrow-in-right"></i> Login Admin
    </a>
</nav>

{{-- ══ HERO ══ --}}
<div class="hero">
    <div class="hero-inner">
        <h1><i class="bi bi-bookmark-star-fill me-2" style="opacity:.85;"></i>Rujukan Jurnal APJI</h1>
        <p class="hero-sub">Sistem Rujukan dan Database Jurnal Nasional</p>
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <span class="stat-pill"><i class="bi bi-collection-fill"></i> {{ number_format($totalCount) }} Referensi</span>
            @if($jenisOptions->count())
            <span class="stat-pill"><i class="bi bi-bookmarks-fill"></i> {{ $jenisOptions->count() }} Jenis</span>
            @endif
            @if($bidangOptions->count())
            <span class="stat-pill"><i class="bi bi-diagram-3-fill"></i> {{ $bidangOptions->count() }} Bidang Ilmu</span>
            @endif
            @if($tahunOptions->count())
            <span class="stat-pill"><i class="bi bi-calendar3-fill"></i> {{ $tahunOptions->min() }}–{{ $tahunOptions->max() }}</span>
            @endif
        </div>
    </div>
</div>

{{-- ══ MAIN LAYOUT ══ --}}
<div class="layout">

    {{-- ── SIDEBAR ── --}}
    <aside class="filter-sidebar">
        <div class="filter-sidebar-head">
            <i class="bi bi-funnel-fill"></i> Filter Pencarian
        </div>
        <div class="filter-sidebar-body">
            <form method="GET" action="{{ route('public.referensi-jurnal') }}" id="filterForm">
                <div class="filter-group">
                    <label><i class="bi bi-search"></i> Kata Kunci</label>
                    <input type="text" name="search" class="form-control"
                           placeholder="Nama jurnal, referensi..."
                           value="{{ request('search') }}" autocomplete="off">
                </div>
                <div class="filter-group">
                    <label><i class="bi bi-bookmarks"></i> Jenis Jurnal</label>
                    <select name="jenis_jurnal" class="form-select">
                        <option value="">Semua Jenis</option>
                        @foreach($jenisOptions as $j)
                        <option value="{{ $j }}" {{ request('jenis_jurnal') === $j ? 'selected' : '' }}>{{ $j }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label><i class="bi bi-mortarboard"></i> Bidang Ilmu</label>
                    <select name="bidang_ilmu" class="form-select">
                        <option value="">Semua Bidang</option>
                        @foreach($bidangOptions as $b)
                        <option value="{{ $b }}" {{ request('bidang_ilmu') === $b ? 'selected' : '' }}>{{ $b }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label><i class="bi bi-calendar3"></i> Tahun</label>
                    <select name="tahun" class="form-select">
                        <option value="">Semua Tahun</option>
                        @foreach($tahunOptions as $t)
                        <option value="{{ $t }}" {{ request('tahun') == $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-filter">
                    <i class="bi bi-search me-1"></i>Terapkan Filter
                </button>
                @if(request()->hasAny(['search','jenis_jurnal','bidang_ilmu','tahun']))
                <a href="{{ route('public.referensi-jurnal') }}" class="btn-reset">
                    <i class="bi bi-x-circle me-1"></i>Reset Filter
                </a>
                <div class="af-section">
                    <div class="af-label">Filter aktif</div>
                    @if(request('search'))
                    <span class="af-pill"><i class="bi bi-search" style="font-size:.63rem;"></i>"{{ Str::limit(request('search'),16) }}"<a href="{{ request()->fullUrlWithoutQuery(['search']) }}">&times;</a></span>
                    @endif
                    @if(request('jenis_jurnal'))
                    <span class="af-pill">{{ Str::limit(request('jenis_jurnal'),16) }}<a href="{{ request()->fullUrlWithoutQuery(['jenis_jurnal']) }}">&times;</a></span>
                    @endif
                    @if(request('bidang_ilmu'))
                    <span class="af-pill">{{ Str::limit(request('bidang_ilmu'),16) }}<a href="{{ request()->fullUrlWithoutQuery(['bidang_ilmu']) }}">&times;</a></span>
                    @endif
                    @if(request('tahun'))
                    <span class="af-pill">{{ request('tahun') }}<a href="{{ request()->fullUrlWithoutQuery(['tahun']) }}">&times;</a></span>
                    @endif
                </div>
                @endif
            </form>
        </div>
    </aside>

    {{-- ── MAIN CONTENT ── --}}
    <main>
        {{-- Result bar --}}
        <div class="result-bar">
            @if($referensiJurnals->total() > 0)
            <span>Menampilkan <strong>{{ $referensiJurnals->firstItem() }}–{{ $referensiJurnals->lastItem() }}</strong> dari <strong>{{ number_format($referensiJurnals->total()) }}</strong> referensi</span>
            <span style="font-size:.78rem;">Hal. {{ $referensiJurnals->currentPage() }}/{{ $referensiJurnals->lastPage() }}</span>
            @else
            <span>Tidak ada hasil</span>
            @endif
        </div>

        {{-- Cards --}}
        @forelse($referensiJurnals as $item)
        @php
            $jenis     = $item->jenis_jurnal ?? '';
            $isInt     = str_contains(strtolower($jenis), 'internasional');
            $isNas     = str_contains(strtolower($jenis), 'nasional');
            $strip     = $isInt ? 'background:linear-gradient(90deg,#f59e0b,#fbbf24)'
                                : ($isNas ? 'background:linear-gradient(90deg,#4f46e5,#818cf8)'
                                          : 'background:#9ca3af');
            $numCls    = $isInt ? 'ref-number-int' : ($isNas ? 'ref-number-nas' : 'ref-number-oth');
            $badgeCls  = $isInt ? 'badge-int'      : ($isNas ? 'badge-nas'      : 'badge-oth');
            $no        = $loop->iteration + ($referensiJurnals->currentPage()-1)*$referensiJurnals->perPage();

            // Build cite formats
            $formats = [];
            if ($item->kutipan) $formats['Referensi'] = $item->kutipan;
            if ($item->format_sitasi) {
                foreach ($item->format_sitasi as $k => $v) {
                    if (trim($v)) $formats[$k] = trim($v);
                }
            }
            $uid = 'ct'.$item->id;
        @endphp

        <article class="ref-card">
            <div class="ref-card-strip" style="{{ $strip }}"></div>
            <div class="ref-card-body">

                {{-- Header --}}
                <div class="d-flex align-items-start mb-1">
                    <span class="ref-number {{ $numCls }}">{{ $no }}</span>
                    <div class="flex-fill">
                        <div class="ref-title mb-2">{{ $item->nama_jurnal }}</div>
                        <div class="d-flex flex-wrap gap-1">
                            <span class="{{ $badgeCls }}">{{ $jenis ?: 'Lainnya' }}</span>
                            @if($item->bidang_ilmu)
                            <span class="badge-meta"><i class="bi bi-mortarboard me-1"></i>{{ $item->bidang_ilmu }}</span>
                            @endif
                            <span class="badge-meta"><i class="bi bi-calendar3 me-1"></i>{{ $item->tahun }}</span>
                        </div>
                    </div>
                </div>

                <hr class="ref-divider">

                {{-- Referensi --}}
                <div class="mb-0">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="section-label label-ref">
                            <i class="bi bi-file-text-fill"></i> Referensi
                        </div>
                        <button class="copy-btn" data-text="{{ $item->referensi }}" onclick="copyText(this)">
                            <i class="bi bi-clipboard"></i> Salin
                        </button>
                    </div>
                    <div class="ref-text-wrap">
                        <p class="ref-text ref-linkified" data-raw="{{ $item->referensi }}">{{ $item->referensi }}</p>
                    </div>
                </div>

                {{-- Citation formats --}}
                @if(count($formats) > 0)
                <div class="cite-panel mt-3">
                    <div class="cite-panel-head">
                        <i class="bi bi-braces"></i>
                        Format Sitasi
                        <span class="cite-count">{{ count($formats) }} format</span>
                    </div>
                    <div class="cite-body">
                        <div class="cite-tabs">
                            @foreach($formats as $fmt => $txt)
                            <button class="cite-tab {{ $loop->first ? 'active' : '' }}"
                                    onclick="switchCiteTab('{{ $uid }}', {{ $loop->index }}, this)"
                                    type="button">{{ $fmt }}</button>
                            @endforeach
                        </div>
                        @foreach($formats as $fmt => $txt)
                        <div id="{{ $uid }}_p{{ $loop->index }}" class="{{ $loop->first ? '' : 'd-none' }}">
                            <div class="kut-text-wrap">
                                <p class="kut-text">{{ $txt }}</p>
                            </div>
                            <button class="copy-btn mt-2" data-text="{{ $txt }}" onclick="copyText(this)">
                                <i class="bi bi-clipboard"></i> Salin {{ $fmt }}
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>
                @elseif($item->kutipan)
                <div class="mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="section-label" style="color:#7c3aed;">
                            <i class="bi bi-braces"></i> Kutipan
                        </div>
                        <button class="copy-btn" data-text="{{ $item->kutipan }}" onclick="copyText(this)">
                            <i class="bi bi-clipboard"></i> Salin
                        </button>
                    </div>
                    <div class="kut-text-wrap">
                        <p class="kut-text">{{ $item->kutipan }}</p>
                    </div>
                </div>
                @endif

            </div>
        </article>

        @empty
        <div class="empty-state">
            <i class="bi bi-bookmark-x"></i>
            <div class="fw-bold mb-2" style="color:#374151;">Referensi tidak ditemukan</div>
            <p class="mb-3" style="font-size:.87rem;">
                @if(request()->hasAny(['search','jenis_jurnal','bidang_ilmu','tahun']))
                    Tidak ada referensi yang cocok dengan filter dipilih.
                @else
                    Belum ada data referensi jurnal.
                @endif
            </p>
            @if(request()->hasAny(['search','jenis_jurnal','bidang_ilmu','tahun']))
            <a href="{{ route('public.referensi-jurnal') }}" class="btn-reset d-inline-block" style="width:auto;padding:6px 20px;">
                <i class="bi bi-x-circle me-1"></i>Reset Filter
            </a>
            @endif
        </div>
        @endforelse

        {{-- Pagination --}}
        @if($referensiJurnals->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $referensiJurnals->links() }}
        </div>
        @endif

        <div class="page-footer">
            &copy; {{ date('Y') }} <strong>{{ $settings['app_name'] }}</strong> &mdash; Data referensi jurnal ilmiah
        </div>
    </main>

</div>{{-- .layout --}}

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ── Scroll: progress bar + topbar shadow + back-to-top ── */
const topbar   = document.getElementById('topbar');
const progress = document.getElementById('scroll-progress');
const backTop  = document.getElementById('back-top');

window.addEventListener('scroll', () => {
    const scrollY   = window.scrollY;
    const docHeight = document.documentElement.scrollHeight - window.innerHeight;
    const pct       = docHeight > 0 ? (scrollY / docHeight) * 100 : 0;

    progress.style.width = pct + '%';
    topbar.classList.toggle('scrolled', scrollY > 20);
    backTop.classList.toggle('show', scrollY > 300);
}, { passive: true });

/* ── Copy to clipboard ── */
function copyText(btn) {
    navigator.clipboard.writeText(btn.dataset.text).then(() => {
        const orig = btn.innerHTML;
        btn.classList.add('copied');
        btn.innerHTML = '<i class="bi bi-check2-circle"></i> Tersalin!';
        setTimeout(() => { btn.classList.remove('copied'); btn.innerHTML = orig; }, 2000);
    });
}

/* ── Linkify URL/DOI dalam teks referensi ── */
document.querySelectorAll('.ref-linkified').forEach(el => {
    const raw = el.dataset.raw || el.textContent;
    el.innerHTML = raw.replace(/((https?:\/\/|doi\.org\/)[^\s,;)\]]+)/gi, m => {
        const href = m.startsWith('http') ? m : 'https://' + m;
        return `<a href="${href}" target="_blank" rel="noopener" class="ref-doi">${m}</a>`;
    });
});

/* ── Citation panel toggle ── */
function toggleCite(uid) {
    const body = document.getElementById(uid);
    const chv  = document.getElementById(uid + '_chv');
    const open = body.style.display !== 'none';
    body.style.display = open ? 'none' : '';
    chv.className = open ? 'bi bi-chevron-down' : 'bi bi-chevron-up';
}

/* ── Citation tab switch ── */
function switchCiteTab(uid, idx, btn) {
    btn.closest('.cite-tabs').querySelectorAll('.cite-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    const parent = document.getElementById(uid);
    parent.querySelectorAll('[id^="' + uid + '_p"]').forEach(p => p.classList.add('d-none'));
    document.getElementById(uid + '_p' + idx).classList.remove('d-none');
}

/* ── Auto-submit filter on select change ── */
document.querySelectorAll('#filterForm select').forEach(el => {
    el.addEventListener('change', () => document.getElementById('filterForm').submit());
});
</script>
</body>
</html>
