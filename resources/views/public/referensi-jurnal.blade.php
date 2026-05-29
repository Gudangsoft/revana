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
        --gold2: #fbbf24;
        --sidebar-w: 300px;
    }

    *, *::before, *::after { box-sizing: border-box; }
    html { scroll-behavior: smooth; }
    body {
        margin: 0; padding: 0;
        background: #f1f5f9;
        font-family: 'Segoe UI', system-ui, sans-serif;
        min-height: 100vh;
    }

    /* ══ TOPBAR ══ */
    .topbar {
        background: linear-gradient(135deg, var(--primary), var(--primary2));
        height: 56px;
        display: flex; align-items: center;
        padding: 0 24px;
        position: sticky; top: 0; z-index: 200;
        box-shadow: 0 2px 16px rgba(79,70,229,.3);
    }
    .topbar-brand { display: flex; align-items: center; gap: 10px; }
    .topbar-brand img { height: 30px; }
    .topbar-brand span { color: #fff; font-weight: 700; font-size: 1.05rem; letter-spacing: .01em; }
    .topbar-right { margin-left: auto; display: flex; align-items: center; gap: 12px; }
    .topbar-login {
        color: rgba(255,255,255,.9); font-size: .82rem; text-decoration: none;
        display: flex; align-items: center; gap: 6px;
        border: 1px solid rgba(255,255,255,.35); border-radius: 8px;
        padding: 5px 14px; transition: background .15s;
    }
    .topbar-login:hover { background: rgba(255,255,255,.15); color: #fff; }

    /* ══ HERO ══ */
    .hero {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary2) 60%, #6d28d9 100%);
        padding: 36px 24px 72px;
        position: relative; overflow: hidden;
    }
    .hero::before {
        content: '';
        position: absolute; top: -60px; right: -60px;
        width: 320px; height: 320px;
        background: rgba(255,255,255,.06);
        border-radius: 50%;
    }
    .hero::after {
        content: '';
        position: absolute; bottom: -80px; left: 10%;
        width: 200px; height: 200px;
        background: rgba(255,255,255,.05);
        border-radius: 50%;
    }
    .hero-inner { position: relative; z-index: 1; max-width: 1400px; margin: 0 auto; }
    .hero h1 { color: #fff; font-size: 2rem; font-weight: 800; letter-spacing: -.01em; margin-bottom: 6px; }
    .hero-sub { color: rgba(255,255,255,.78); font-size: .92rem; margin-bottom: 20px; }
    .stat-pill {
        display: inline-flex; align-items: center; gap: 6px;
        background: rgba(255,255,255,.14);
        border: 1px solid rgba(255,255,255,.2);
        backdrop-filter: blur(6px);
        border-radius: 999px; padding: 5px 14px;
        font-size: .8rem; font-weight: 600; color: #fff;
    }

    /* ══ BODY LAYOUT ══ */
    .page-body {
        max-width: 1400px; margin: 0 auto;
        padding: 0 20px 60px;
        display: grid;
        grid-template-columns: var(--sidebar-w) 1fr;
        gap: 24px;
        align-items: start;
        margin-top: -44px; /* overlap hero */
        position: relative; z-index: 10;
    }

    /* ══ SIDEBAR ══ */
    .filter-sidebar {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0,0,0,.09);
        overflow: hidden;
        position: sticky; top: 68px;
    }
    .filter-sidebar-head {
        background: linear-gradient(135deg, var(--primary), var(--primary2));
        padding: 14px 18px;
        color: #fff; font-weight: 700; font-size: .9rem;
        display: flex; align-items: center; gap: 8px;
    }
    .filter-sidebar-body { padding: 16px; }
    .filter-group { margin-bottom: 16px; }
    .filter-group label {
        display: block; font-size: .75rem; font-weight: 700;
        color: #6b7280; text-transform: uppercase; letter-spacing: .05em;
        margin-bottom: 6px;
    }
    .filter-group .form-control,
    .filter-group .form-select {
        border-radius: 8px; border-color: #e5e7eb;
        font-size: .85rem; padding: 8px 10px;
        transition: border-color .15s, box-shadow .15s;
    }
    .filter-group .form-control:focus,
    .filter-group .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(99,102,241,.12);
    }
    .btn-filter {
        width: 100%; border-radius: 10px; font-weight: 600;
        background: linear-gradient(135deg, var(--primary), var(--primary2));
        border: none; color: #fff; padding: 9px;
        font-size: .88rem; cursor: pointer;
        transition: opacity .15s;
    }
    .btn-filter:hover { opacity: .88; }
    .btn-reset {
        width: 100%; border-radius: 10px; font-weight: 600;
        background: #f3f4f6; border: 1px solid #e5e7eb;
        color: #6b7280; padding: 7px; font-size: .82rem;
        text-decoration: none; display: block; text-align: center;
        margin-top: 8px; transition: background .15s;
    }
    .btn-reset:hover { background: #e5e7eb; color: #374151; }

    /* Active filter pills in sidebar */
    .af-pill {
        display: inline-flex; align-items: center; gap: 5px;
        background: #ede9fe; color: #4338ca;
        border-radius: 999px; font-size: .72rem; font-weight: 600;
        padding: 2px 9px; margin: 2px;
    }
    .af-pill a { color: #6366f1; text-decoration: none; line-height: 1; }
    .af-pill a:hover { color: #ef4444; }

    /* ══ MAIN CONTENT ══ */
    .main-col {}

    /* Result bar */
    .result-bar {
        background: #fff;
        border-radius: 12px;
        padding: 10px 16px;
        margin-bottom: 14px;
        display: flex; align-items: center; justify-content: space-between;
        box-shadow: 0 1px 4px rgba(0,0,0,.06);
        font-size: .83rem; color: #6b7280;
    }
    .result-bar strong { color: #111827; }

    /* ══ REF CARD ══ */
    .ref-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 1px 6px rgba(0,0,0,.07);
        margin-bottom: 14px;
        overflow: hidden;
        transition: box-shadow .18s, transform .18s;
        border: 1px solid #f1f5f9;
    }
    .ref-card:hover {
        box-shadow: 0 8px 28px rgba(79,70,229,.13);
        transform: translateY(-2px);
    }

    /* Card top strip */
    .ref-card-strip {
        height: 4px;
    }

    .ref-card-body { padding: 18px 20px; }

    /* Nomor */
    .ref-number {
        display: inline-flex; align-items: center; justify-content: center;
        width: 28px; height: 28px; border-radius: 8px;
        font-size: .75rem; font-weight: 800;
        flex-shrink: 0; margin-right: 10px; margin-top: 2px;
    }
    .ref-number-nas { background: #ede9fe; color: #5b21b6; }
    .ref-number-int { background: #fef3c7; color: #92400e; }
    .ref-number-oth { background: #f3f4f6; color: #6b7280; }

    /* Judul & badges */
    .ref-title {
        font-size: .97rem; font-weight: 700; color: #111827; line-height: 1.4;
    }
    .badge-nas {
        background: linear-gradient(135deg, var(--primary), #818cf8);
        color: #fff; font-size: .68rem; padding: 3px 9px; border-radius: 8px;
        font-weight: 600;
    }
    .badge-int {
        background: linear-gradient(135deg, var(--gold), var(--gold2));
        color: #fff; font-size: .68rem; padding: 3px 9px; border-radius: 8px;
        font-weight: 600;
    }
    .badge-oth {
        background: #e5e7eb; color: #4b5563;
        font-size: .68rem; padding: 3px 9px; border-radius: 8px;
        font-weight: 600;
    }
    .badge-meta {
        background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0;
        font-size: .68rem; padding: 2px 8px; border-radius: 7px; font-weight: 500;
    }

    /* Divider */
    .ref-divider {
        border: none; border-top: 1px dashed #e5e7eb; margin: 12px 0;
    }

    /* Section label */
    .section-label {
        font-size: .68rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .06em;
        display: flex; align-items: center; gap: 5px;
        margin-bottom: 6px;
    }
    .label-ref  { color: #4f46e5; }
    .label-kut  { color: #7c3aed; }

    /* Teks referensi */
    .ref-text {
        font-size: .87rem; line-height: 1.75; color: #1f2937;
        background: #f8f9ff;
        border: 1px solid #e0e7ff;
        border-radius: 8px;
        padding: 10px 14px;
    }

    /* Teks kutipan */
    .kut-text {
        font-size: .84rem; line-height: 1.7; color: #3b0764;
        background: #faf5ff;
        border-left: 3px solid #a78bfa;
        border-radius: 0 8px 8px 0;
        padding: 10px 14px;
        font-style: italic;
    }

    /* Copy btn */
    .copy-btn {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px; font-size: .72rem; font-weight: 500;
        border-radius: 7px; border: 1px solid #d1d5db;
        background: #fff; color: #4b5563;
        cursor: pointer; transition: all .15s; flex-shrink: 0;
    }
    .copy-btn:hover  { background: #f3f4f6; border-color: #9ca3af; }
    .copy-btn.copied { background: #d1fae5; border-color: #34d399; color: #065f46; }

    /* ══ PAGINATION ══ */
    .pagination { gap: 4px; }
    .page-link {
        border-radius: 9px !important; border: 1px solid #e5e7eb;
        color: var(--primary); font-size: .83rem; min-width: 36px;
        text-align: center; transition: all .15s;
    }
    .page-item.active .page-link {
        background: linear-gradient(135deg, var(--primary), var(--primary2));
        border-color: transparent; color: #fff;
        box-shadow: 0 3px 8px rgba(99,102,241,.35);
    }
    .page-link:hover { background: #ede9fe; border-color: #c7d2fe; }

    /* ══ EMPTY ══ */
    .empty-state {
        text-align: center; padding: 64px 20px; color: #9ca3af;
        background: #fff; border-radius: 14px;
        box-shadow: 0 1px 6px rgba(0,0,0,.06);
    }
    .empty-state i { font-size: 3.5rem; display: block; margin-bottom: 14px; opacity: .35; }

    /* ══ FOOTER ══ */
    .page-footer {
        text-align: center; font-size: .78rem;
        color: #94a3b8; padding: 24px 0 8px;
    }

    /* ══ RESPONSIVE ══ */
    @media (max-width: 768px) {
        .page-body {
            grid-template-columns: 1fr;
            margin-top: -20px;
        }
        .filter-sidebar { position: static; }
        .hero h1 { font-size: 1.5rem; }
        .hero { padding: 28px 16px 56px; }
    }
    </style>
</head>
<body>

{{-- ══ TOPBAR ══ --}}
<div class="topbar">
    <div class="topbar-brand">
        @if($settings['logo'])
            <img src="{{ asset('storage/' . $settings['logo']) }}" alt="Logo">
        @else
            <i class="bi bi-journal-check text-white fs-5"></i>
        @endif
        <span>{{ $settings['app_name'] }}</span>
    </div>
    <div class="topbar-right">
        <a href="{{ route('login') }}" class="topbar-login">
            <i class="bi bi-box-arrow-in-right"></i> Login Admin
        </a>
    </div>
</div>

{{-- ══ HERO ══ --}}
<div class="hero">
    <div class="hero-inner">
        <h1><i class="bi bi-bookmark-star-fill me-2" style="opacity:.85;"></i>Daftar Referensi Jurnal</h1>
        <p class="hero-sub">{{ $settings['full_name'] ?: ($settings['tagline'] ?: 'Sistem Informasi Peer Review Artikel') }}</p>
        <div class="d-flex flex-wrap gap-2">
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

{{-- ══ PAGE BODY ══ --}}
<div class="page-body">

    {{-- ── SIDEBAR FILTER ── --}}
    <aside class="filter-sidebar">
        <div class="filter-sidebar-head">
            <i class="bi bi-funnel-fill"></i> Filter Pencarian
        </div>
        <div class="filter-sidebar-body">
            <form method="GET" action="{{ route('public.referensi-jurnal') }}" id="filterForm">

                <div class="filter-group">
                    <label><i class="bi bi-search me-1"></i>Kata Kunci</label>
                    <input type="text" name="search" class="form-control"
                           placeholder="Nama jurnal, referensi..."
                           value="{{ request('search') }}" autocomplete="off">
                </div>

                <div class="filter-group">
                    <label><i class="bi bi-bookmarks me-1"></i>Jenis Jurnal</label>
                    <select name="jenis_jurnal" class="form-select">
                        <option value="">Semua Jenis</option>
                        @foreach($jenisOptions as $j)
                        <option value="{{ $j }}" {{ request('jenis_jurnal') === $j ? 'selected' : '' }}>{{ $j }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label><i class="bi bi-mortarboard me-1"></i>Bidang Ilmu</label>
                    <select name="bidang_ilmu" class="form-select">
                        <option value="">Semua Bidang</option>
                        @foreach($bidangOptions as $b)
                        <option value="{{ $b }}" {{ request('bidang_ilmu') === $b ? 'selected' : '' }}>{{ $b }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label><i class="bi bi-calendar3 me-1"></i>Tahun</label>
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
                    <i class="bi bi-x-circle me-1"></i>Reset Semua Filter
                </a>

                {{-- Active filters --}}
                <div class="mt-3 pt-3" style="border-top:1px dashed #e5e7eb;">
                    <div class="text-muted mb-2" style="font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em;">Filter Aktif</div>
                    @if(request('search'))
                    <span class="af-pill"><i class="bi bi-search" style="font-size:.65rem;"></i>"{{ Str::limit(request('search'),18) }}"<a href="{{ request()->fullUrlWithoutQuery(['search']) }}">&times;</a></span>
                    @endif
                    @if(request('jenis_jurnal'))
                    <span class="af-pill"><i class="bi bi-bookmarks" style="font-size:.65rem;"></i>{{ Str::limit(request('jenis_jurnal'),16) }}<a href="{{ request()->fullUrlWithoutQuery(['jenis_jurnal']) }}">&times;</a></span>
                    @endif
                    @if(request('bidang_ilmu'))
                    <span class="af-pill"><i class="bi bi-mortarboard" style="font-size:.65rem;"></i>{{ Str::limit(request('bidang_ilmu'),16) }}<a href="{{ request()->fullUrlWithoutQuery(['bidang_ilmu']) }}">&times;</a></span>
                    @endif
                    @if(request('tahun'))
                    <span class="af-pill"><i class="bi bi-calendar3" style="font-size:.65rem;"></i>{{ request('tahun') }}<a href="{{ request()->fullUrlWithoutQuery(['tahun']) }}">&times;</a></span>
                    @endif
                </div>
                @endif
            </form>
        </div>
    </aside>

    {{-- ── MAIN CONTENT ── --}}
    <main class="main-col">

        {{-- Result bar --}}
        <div class="result-bar">
            @if($referensiJurnals->total() > 0)
            <span>
                Menampilkan <strong>{{ $referensiJurnals->firstItem() }}–{{ $referensiJurnals->lastItem() }}</strong>
                dari <strong>{{ number_format($referensiJurnals->total()) }}</strong> referensi
            </span>
            <span>Hal. {{ $referensiJurnals->currentPage() }}/{{ $referensiJurnals->lastPage() }}</span>
            @else
            <span>Tidak ada hasil ditemukan</span>
            @endif
        </div>

        {{-- List --}}
        @forelse($referensiJurnals as $item)
        @php
            $jenis     = $item->jenis_jurnal ?? '';
            $isInt     = str_contains(strtolower($jenis), 'internasional');
            $isNas     = str_contains(strtolower($jenis), 'nasional');
            $strip     = $isInt ? 'background:linear-gradient(90deg,#f59e0b,#fbbf24)' : ($isNas ? 'background:linear-gradient(90deg,#4f46e5,#818cf8)' : 'background:#9ca3af');
            $numClass  = $isInt ? 'ref-number-int' : ($isNas ? 'ref-number-nas' : 'ref-number-oth');
            $badgeCls  = $isInt ? 'badge-int' : ($isNas ? 'badge-nas' : 'badge-oth');
            $no        = $loop->iteration + ($referensiJurnals->currentPage()-1)*$referensiJurnals->perPage();
        @endphp

        <div class="ref-card">
            {{-- Color strip top --}}
            <div class="ref-card-strip" style="{{ $strip }}"></div>

            <div class="ref-card-body">
                {{-- Header --}}
                <div class="d-flex align-items-start gap-0 mb-12">
                    <span class="ref-number {{ $numClass }}">{{ $no }}</span>
                    <div class="flex-fill" style="margin-bottom:10px;">
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
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="section-label label-ref">
                            <i class="bi bi-file-text-fill"></i> Referensi
                        </div>
                        <button class="copy-btn" data-text="{{ $item->referensi }}" onclick="copyText(this)">
                            <i class="bi bi-clipboard"></i> Salin
                        </button>
                    </div>
                    <div class="ref-text">{{ $item->referensi }}</div>
                </div>

                {{-- Kutipan --}}
                @if($item->kutipan)
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="section-label label-kut">
                            <i class="bi bi-quote"></i> Kutipan
                        </div>
                        <button class="copy-btn" data-text="{{ $item->kutipan }}" onclick="copyText(this)">
                            <i class="bi bi-clipboard"></i> Salin
                        </button>
                    </div>
                    <div class="kut-text">{{ $item->kutipan }}</div>
                </div>
                @endif
            </div>
        </div>

        @empty
        <div class="empty-state">
            <i class="bi bi-bookmark-x"></i>
            <div class="fw-bold mb-2" style="color:#374151;">Referensi tidak ditemukan</div>
            <p class="mb-3" style="color:#9ca3af; font-size:.87rem;">
                @if(request()->hasAny(['search','jenis_jurnal','bidang_ilmu','tahun']))
                    Tidak ada referensi yang cocok dengan filter yang dipilih.
                @else
                    Belum ada data referensi jurnal yang tersedia.
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

        {{-- Footer --}}
        <div class="page-footer">
            &copy; {{ date('Y') }} <strong>{{ $settings['app_name'] }}</strong>
            &mdash; Data referensi jurnal ilmiah
        </div>
    </main>

</div>{{-- end page-body --}}

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function copyText(btn) {
    const text = btn.dataset.text;
    navigator.clipboard.writeText(text).then(() => {
        const orig = btn.innerHTML;
        btn.classList.add('copied');
        btn.innerHTML = '<i class="bi bi-check2-circle"></i> Tersalin!';
        setTimeout(() => { btn.classList.remove('copied'); btn.innerHTML = orig; }, 2000);
    });
}
/* Auto-submit filter on select change */
document.querySelectorAll('#filterForm select').forEach(el => {
    el.addEventListener('change', () => document.getElementById('filterForm').submit());
});
</script>
</body>
</html>
