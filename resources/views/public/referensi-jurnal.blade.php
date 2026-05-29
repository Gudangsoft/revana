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
            --bc: #4f46e5;
            --bc2: #7c3aed;
        }
        body { background: #f3f4f6; font-family: 'Segoe UI', sans-serif; min-height: 100vh; }

        /* ── Navbar ── */
        .top-nav {
            background: linear-gradient(135deg, var(--bc), var(--bc2));
            padding: 12px 0;
            position: sticky; top: 0; z-index: 100;
            box-shadow: 0 2px 12px rgba(79,70,229,.25);
        }
        .brand-logo { height: 36px; }
        .back-btn {
            color: rgba(255,255,255,.85); font-size: .85rem; text-decoration: none;
            display: inline-flex; align-items: center; gap: 6px;
            transition: color .15s;
        }
        .back-btn:hover { color: #fff; }

        /* ── Hero ── */
        .hero {
            background: linear-gradient(135deg, var(--bc) 0%, var(--bc2) 100%);
            color: #fff; padding: 40px 0 32px;
        }
        .hero h1 { font-size: 1.8rem; font-weight: 700; }
        .hero p   { opacity: .85; font-size: .95rem; }
        .stat-pill {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,.15); border-radius: 999px;
            padding: 5px 14px; font-size: .82rem; font-weight: 600;
            backdrop-filter: blur(4px);
        }

        /* ── Filter Card ── */
        .filter-card {
            border: none; border-radius: 14px;
            box-shadow: 0 4px 20px rgba(0,0,0,.08);
            margin-top: -28px; position: relative; z-index: 10;
        }

        /* ── Item Card ── */
        .ref-item {
            border: none; border-radius: 12px;
            box-shadow: 0 1px 6px rgba(0,0,0,.07);
            transition: transform .15s, box-shadow .15s;
            overflow: hidden;
        }
        .ref-item:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.11); }
        .ref-item .item-accent {
            width: 4px; flex-shrink: 0; border-radius: 4px 0 0 4px;
        }
        .badge-nas  { background: linear-gradient(135deg,#6366f1,#818cf8); color:#fff; }
        .badge-int  { background: linear-gradient(135deg,#f59e0b,#fbbf24); color:#fff; }
        .badge-oth  { background: linear-gradient(135deg,#6b7280,#9ca3af); color:#fff; }
        .ref-text-block {
            font-size: .83rem; line-height: 1.65; color: #374151;
        }
        .kutipan-block {
            font-size: .80rem; line-height: 1.6; color: #4b5563;
            background: #f5f3ff; border-left: 3px solid #c4b5fd;
            padding: 8px 12px; border-radius: 0 6px 6px 0;
            font-style: italic;
        }
        .copy-btn {
            padding: 2px 9px; font-size: .72rem; border-radius: 6px;
            border: 1px solid #d1d5db; background: #f9fafb;
            cursor: pointer; transition: background .15s;
            display: inline-flex; align-items: center; gap: 4px;
        }
        .copy-btn:hover  { background: #e5e7eb; }
        .copy-btn.copied { background: #d1fae5; border-color: #6ee7b7; color: #065f46; }

        /* ── Pagination ── */
        .page-link { border-radius: 8px !important; margin: 0 2px; border: none; color: var(--bc); }
        .page-item.active .page-link { background: var(--bc); color: #fff; }
        .page-link:hover { background: #ede9fe; color: var(--bc2); }

        /* ── Empty ── */
        .empty-box { padding: 60px 20px; text-align: center; color: #9ca3af; }
        .empty-box i { font-size: 3rem; display: block; margin-bottom: 12px; opacity: .4; }

        /* ── Active filter pill ── */
        .af-pill {
            display: inline-flex; align-items: center; gap: 5px;
            background: #ede9fe; color: #4338ca; border-radius: 999px;
            font-size: .74rem; font-weight: 600; padding: 3px 10px;
        }
        .af-pill a { color: #6366f1; text-decoration: none; font-size: .85rem; }
        .af-pill a:hover { color: #ef4444; }

        @media (max-width: 576px) {
            .hero h1 { font-size: 1.4rem; }
        }
    </style>
</head>
<body>

{{-- ── Navbar ── --}}
<nav class="top-nav">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            @if($settings['logo'])
            <img src="{{ asset('storage/' . $settings['logo']) }}" alt="Logo" class="brand-logo">
            @else
            <i class="bi bi-journal-check text-white fs-4"></i>
            @endif
            <span class="text-white fw-bold fs-5">{{ $settings['app_name'] }}</span>
        </div>
        <a href="{{ route('login') }}" class="back-btn">
            <i class="bi bi-box-arrow-in-right"></i> Login Admin
        </a>
    </div>
</nav>

{{-- ── Hero ── --}}
<div class="hero">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h1 class="mb-1">
                    <i class="bi bi-bookmark-star-fill me-2" style="opacity:.9;"></i>Daftar Referensi Jurnal
                </h1>
                <p class="mb-3">
                    {{ $settings['full_name'] ?: ($settings['tagline'] ?: 'Sistem Informasi Peer Review Artikel') }}
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <span class="stat-pill">
                        <i class="bi bi-collection-fill"></i> {{ number_format($totalCount) }} Referensi
                    </span>
                    @if($jenisOptions->count())
                    <span class="stat-pill">
                        <i class="bi bi-bookmarks-fill"></i> {{ $jenisOptions->count() }} Jenis
                    </span>
                    @endif
                    @if($bidangOptions->count())
                    <span class="stat-pill">
                        <i class="bi bi-diagram-3-fill"></i> {{ $bidangOptions->count() }} Bidang Ilmu
                    </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5" style="max-width:880px;">

    {{-- ── Filter Card (overlapping hero) ── --}}
    <div class="card filter-card mb-4">
        <div class="card-body py-3 px-4">
            <form method="GET" action="{{ route('public.referensi-jurnal') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-5">
                        <label class="form-label small fw-semibold text-muted mb-1">
                            <i class="bi bi-search"></i> Cari
                        </label>
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="Nama jurnal, referensi, kutipan..."
                               value="{{ request('search') }}" autocomplete="off">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-semibold text-muted mb-1">
                            <i class="bi bi-bookmarks"></i> Jenis
                        </label>
                        <select name="jenis_jurnal" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            @foreach($jenisOptions as $j)
                            <option value="{{ $j }}" {{ request('jenis_jurnal') === $j ? 'selected' : '' }}>{{ $j }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-semibold text-muted mb-1">
                            <i class="bi bi-mortarboard"></i> Bidang
                        </label>
                        <select name="bidang_ilmu" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            @foreach($bidangOptions as $b)
                            <option value="{{ $b }}" {{ request('bidang_ilmu') === $b ? 'selected' : '' }}>{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-1">
                        <label class="form-label small fw-semibold text-muted mb-1">
                            <i class="bi bi-calendar3"></i> Tahun
                        </label>
                        <select name="tahun" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            @foreach($tahunOptions as $t)
                            <option value="{{ $t }}" {{ request('tahun') == $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill">
                            <i class="bi bi-search"></i> Cari
                        </button>
                        @if(request()->hasAny(['search','jenis_jurnal','bidang_ilmu','tahun']))
                        <a href="{{ route('public.referensi-jurnal') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                            <i class="bi bi-x-lg"></i>
                        </a>
                        @endif
                    </div>
                </div>

                {{-- Active filter pills --}}
                @if(request()->hasAny(['search','jenis_jurnal','bidang_ilmu','tahun']))
                <div class="mt-2 d-flex flex-wrap gap-1 align-items-center">
                    <span class="small text-muted me-1">Filter aktif:</span>
                    @if(request('search'))
                    <span class="af-pill">
                        <i class="bi bi-search" style="font-size:.7rem;"></i>
                        "{{ Str::limit(request('search'), 25) }}"
                        <a href="{{ request()->fullUrlWithoutQuery(['search']) }}">&times;</a>
                    </span>
                    @endif
                    @if(request('jenis_jurnal'))
                    <span class="af-pill">
                        <i class="bi bi-bookmarks" style="font-size:.7rem;"></i>
                        {{ request('jenis_jurnal') }}
                        <a href="{{ request()->fullUrlWithoutQuery(['jenis_jurnal']) }}">&times;</a>
                    </span>
                    @endif
                    @if(request('bidang_ilmu'))
                    <span class="af-pill">
                        <i class="bi bi-mortarboard" style="font-size:.7rem;"></i>
                        {{ request('bidang_ilmu') }}
                        <a href="{{ request()->fullUrlWithoutQuery(['bidang_ilmu']) }}">&times;</a>
                    </span>
                    @endif
                    @if(request('tahun'))
                    <span class="af-pill">
                        <i class="bi bi-calendar3" style="font-size:.7rem;"></i>
                        {{ request('tahun') }}
                        <a href="{{ request()->fullUrlWithoutQuery(['tahun']) }}">&times;</a>
                    </span>
                    @endif
                </div>
                @endif
            </form>
        </div>
    </div>

    {{-- ── Hasil ── --}}
    @if($referensiJurnals->total() > 0)
    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="text-muted small">
            Menampilkan <strong>{{ $referensiJurnals->firstItem() }}–{{ $referensiJurnals->lastItem() }}</strong>
            dari <strong>{{ number_format($referensiJurnals->total()) }}</strong> referensi
        </span>
        <span class="text-muted small">Halaman {{ $referensiJurnals->currentPage() }}/{{ $referensiJurnals->lastPage() }}</span>
    </div>
    @endif

    {{-- ── List Referensi ── --}}
    <div class="d-flex flex-column gap-3">
        @forelse($referensiJurnals as $item)
        @php
            $jenis = $item->jenis_jurnal ?? '';
            $isInt = str_contains(strtolower($jenis), 'internasional');
            $isNas = str_contains(strtolower($jenis), 'nasional');
            $badgeClass  = $isInt ? 'badge-int' : ($isNas ? 'badge-nas' : 'badge-oth');
            $accentColor = $isInt ? '#f59e0b' : ($isNas ? '#6366f1' : '#9ca3af');
            $no = $loop->iteration + ($referensiJurnals->currentPage() - 1) * $referensiJurnals->perPage();
        @endphp
        <div class="card ref-item">
            <div class="card-body p-0 d-flex">
                {{-- Accent bar --}}
                <div class="item-accent" style="background:{{ $accentColor }};min-height:100%;"></div>

                <div class="p-3 w-100">
                    {{-- Header --}}
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                        <div class="d-flex align-items-start gap-2 flex-wrap">
                            <span class="text-muted fw-bold" style="font-size:.75rem; min-width:24px;">{{ $no }}.</span>
                            <div>
                                <div class="fw-semibold" style="font-size:.9rem; line-height:1.4; color:#1f2937;">
                                    {{ $item->nama_jurnal }}
                                </div>
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    <span class="badge {{ $badgeClass }}" style="font-size:.7rem; padding:3px 8px; border-radius:7px;">
                                        {{ $jenis ?: 'Lainnya' }}
                                    </span>
                                    @if($item->bidang_ilmu)
                                    <span class="badge bg-light text-dark border" style="font-size:.7rem; padding:3px 8px; border-radius:7px;">
                                        <i class="bi bi-mortarboard me-1"></i>{{ $item->bidang_ilmu }}
                                    </span>
                                    @endif
                                    <span class="badge bg-light text-dark border" style="font-size:.7rem; padding:3px 8px; border-radius:7px;">
                                        <i class="bi bi-calendar3 me-1"></i>{{ $item->tahun }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Referensi --}}
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-semibold text-muted" style="font-size:.72rem; text-transform:uppercase; letter-spacing:.04em;">
                                <i class="bi bi-file-text me-1" style="color:#6366f1;"></i>Referensi
                            </span>
                            <button class="copy-btn" data-text="{{ $item->referensi }}" onclick="copyText(this)">
                                <i class="bi bi-clipboard"></i> Salin
                            </button>
                        </div>
                        <div class="ref-text-block">{{ $item->referensi }}</div>
                    </div>

                    {{-- Kutipan --}}
                    @if($item->kutipan)
                    <div class="mt-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-semibold text-muted" style="font-size:.72rem; text-transform:uppercase; letter-spacing:.04em;">
                                <i class="bi bi-quote me-1" style="color:#7c3aed;"></i>Kutipan
                            </span>
                            <button class="copy-btn" data-text="{{ $item->kutipan }}" onclick="copyText(this)">
                                <i class="bi bi-clipboard"></i> Salin
                            </button>
                        </div>
                        <div class="kutipan-block">{{ $item->kutipan }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="card border-0 shadow-sm" style="border-radius:12px;">
            <div class="empty-box">
                <i class="bi bi-bookmark-x"></i>
                <div class="fw-semibold mb-1">Referensi tidak ditemukan</div>
                <p class="small">
                    @if(request()->hasAny(['search','jenis_jurnal','bidang_ilmu','tahun']))
                        Coba ubah atau hapus filter pencarian.
                        <a href="{{ route('public.referensi-jurnal') }}">Reset filter</a>
                    @else
                        Belum ada data referensi jurnal tersedia.
                    @endif
                </p>
            </div>
        </div>
        @endforelse
    </div>

    {{-- ── Pagination ── --}}
    @if($referensiJurnals->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $referensiJurnals->links() }}
    </div>
    @endif

    {{-- ── Footer ── --}}
    <div class="text-center text-muted small mt-5 pb-3">
        &copy; {{ date('Y') }} {{ $settings['app_name'] }} &mdash; Data referensi jurnal ilmiah
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function copyText(btn) {
    const text = btn.dataset.text;
    navigator.clipboard.writeText(text).then(() => {
        const orig = btn.innerHTML;
        btn.classList.add('copied');
        btn.innerHTML = '<i class="bi bi-check2"></i> Tersalin';
        setTimeout(() => { btn.classList.remove('copied'); btn.innerHTML = orig; }, 1800);
    });
}
</script>
</body>
</html>
