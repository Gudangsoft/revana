<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal Penulis — Cek Status & LOA | SIPERA</title>
    <meta name="description" content="Cek status artikel dan unduh Letter of Acceptance (LOA) Anda. Masukkan Kode SIPERA untuk melihat progress artikel secara real-time.">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --navy:   #0f172a;
            --blue1:  #1a237e;
            --blue2:  #1565c0;
            --sky:    #0288d1;
            --gold:   #f59e0b;
            --green:  #16a34a;
            --grad:   linear-gradient(135deg, #0f172a 0%, #1a237e 50%, #1565c0 100%);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            background: var(--grad);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            padding: 0 0 60px;
            position: relative;
            overflow-x: hidden;
        }

        /* ── Background decoration ── */
        body::before {
            content: '';
            position: fixed; inset: 0; z-index: 0; pointer-events: none;
            background:
                radial-gradient(circle at 15% 20%, rgba(2,136,209,.18) 0%, transparent 45%),
                radial-gradient(circle at 85% 75%, rgba(245,158,11,.10) 0%, transparent 45%),
                radial-gradient(circle at 50% 110%, rgba(22,163,74,.12) 0%, transparent 40%);
        }

        .page-wrap { max-width: 760px; margin: 0 auto; padding: 0 16px; position: relative; z-index: 1; }

        /* ── Top nav bar ── */
        .top-nav {
            display: flex; align-items: center; justify-content: space-between;
            padding: 20px 0 0;
            margin-bottom: 32px;
        }
        .top-nav .brand {
            display: flex; align-items: center; gap: 10px;
            color: #fff; text-decoration: none;
        }
        .top-nav .brand-icon {
            width: 38px; height: 38px; border-radius: 10px;
            background: rgba(255,255,255,.15);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
        }
        .top-nav .brand-name { font-weight: 800; font-size: 1.05rem; letter-spacing: .3px; }
        .top-nav .brand-sub  { font-size: .7rem; opacity: .65; display: block; margin-top: -2px; }
        .top-nav .nav-links  { display: flex; gap: 8px; align-items: center; }
        .top-nav .nav-link-btn {
            color: rgba(255,255,255,.7); font-size: .8rem; text-decoration: none;
            padding: 6px 12px; border-radius: 20px; border: 1px solid rgba(255,255,255,.18);
            transition: all .2s;
        }
        .top-nav .nav-link-btn:hover { color: #fff; background: rgba(255,255,255,.12); border-color: rgba(255,255,255,.35); }

        /* ── Hero ── */
        .hero { text-align: center; padding: 20px 0 36px; }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(245,158,11,.15); border: 1px solid rgba(245,158,11,.4);
            color: #fbbf24; font-size: .75rem; font-weight: 700;
            padding: 5px 14px; border-radius: 20px; letter-spacing: .5px;
            margin-bottom: 18px;
        }
        .hero h1 {
            font-size: clamp(1.8rem, 5vw, 2.6rem);
            font-weight: 900; color: #fff; line-height: 1.15;
            letter-spacing: -.3px;
        }
        .hero h1 span { color: #60a5fa; }
        .hero p {
            color: rgba(255,255,255,.7); font-size: .95rem; margin-top: 10px;
            max-width: 520px; margin-left: auto; margin-right: auto; line-height: 1.6;
        }

        /* ── Search card ── */
        .search-card {
            background: rgba(255,255,255,.97);
            backdrop-filter: blur(20px);
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,.35), 0 1px 0 rgba(255,255,255,.15) inset;
        }
        .search-card-body { padding: 32px 36px 28px; }
        @media (max-width: 576px) { .search-card-body { padding: 24px 20px 20px; } }

        .search-label {
            font-size: .78rem; font-weight: 700; color: #64748b;
            text-transform: uppercase; letter-spacing: .6px;
            margin-bottom: 10px; display: flex; align-items: center; gap: 6px;
        }
        .search-input-wrap {
            display: flex; gap: 10px; align-items: stretch;
        }
        .kode-input {
            flex: 1;
            font-family: 'Cascadia Code', 'Fira Mono', monospace;
            font-size: 1.1rem; letter-spacing: 2px; text-transform: uppercase;
            border: 2px solid #e2e8f0; border-radius: 14px;
            padding: 14px 20px; color: #0f172a;
            transition: border-color .2s, box-shadow .2s;
            background: #f8fafc;
        }
        .kode-input::placeholder { letter-spacing: .5px; color: #94a3b8; font-size: .95rem; }
        .kode-input:focus {
            border-color: var(--blue1); background: #fff;
            box-shadow: 0 0 0 4px rgba(26,35,126,.1); outline: none;
        }
        .btn-cari {
            background: var(--grad); color: #fff;
            border: none; border-radius: 14px;
            padding: 14px 28px; font-weight: 700; font-size: .95rem;
            display: flex; align-items: center; gap: 8px;
            white-space: nowrap; cursor: pointer;
            transition: opacity .2s, transform .15s;
            box-shadow: 0 4px 16px rgba(26,35,126,.35);
        }
        .btn-cari:hover { opacity: .9; transform: translateY(-1px); }
        .search-hint {
            margin-top: 10px; font-size: .75rem; color: #94a3b8;
            display: flex; align-items: center; gap: 6px;
        }

        /* ── Feature pills (when no result) ── */
        .feature-row {
            display: grid; grid-template-columns: repeat(3,1fr); gap: 12px;
            padding: 0 36px 28px;
        }
        @media (max-width: 576px) { .feature-row { grid-template-columns: 1fr; padding: 0 20px 20px; } }
        .feature-pill {
            background: #f8fafc; border: 1px solid #e2e8f0;
            border-radius: 14px; padding: 16px 14px; text-align: center;
        }
        .feature-pill .fp-icon {
            width: 38px; height: 38px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; margin: 0 auto 10px;
        }
        .feature-pill .fp-title { font-weight: 700; font-size: .82rem; color: #0f172a; margin-bottom: 4px; }
        .feature-pill .fp-desc  { font-size: .73rem; color: #64748b; line-height: 1.4; }

        .divider { border: none; border-top: 1px solid #e2e8f0; margin: 0 36px; }
        @media (max-width: 576px) { .divider { margin: 0 20px; } }

        /* ── Result card ── */
        .result-card {
            background: rgba(255,255,255,.97);
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 16px 50px rgba(0,0,0,.3);
            margin-top: 18px;
        }
        .result-header {
            padding: 22px 28px;
            display: flex; align-items: center; gap: 16px;
            position: relative; overflow: hidden;
        }
        .result-header::after {
            content: ''; position: absolute; right: -40px; top: -40px;
            width: 160px; height: 160px; border-radius: 50%;
            background: rgba(255,255,255,.08);
        }
        .journal-logo {
            width: 56px; height: 56px; border-radius: 14px;
            object-fit: cover; border: 3px solid rgba(255,255,255,.35);
            flex-shrink: 0;
        }
        .journal-abbr {
            width: 56px; height: 56px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 900; font-size: 15px;
            background: rgba(255,255,255,.2);
            border: 3px solid rgba(255,255,255,.35);
            color: #fff; flex-shrink: 0;
        }
        .jrn-title { font-size: 1rem; font-weight: 800; line-height: 1.3; color: #fff; }
        .jrn-meta  { font-size: .75rem; color: rgba(255,255,255,.75); margin-top: 3px; }

        .result-body { padding: 24px 28px; }
        @media (max-width: 576px) { .result-body { padding: 18px 18px; } }

        /* ── Article info grid ── */
        .article-info {
            background: #f8fafc; border: 1px solid #e2e8f0;
            border-radius: 14px; padding: 18px 20px; margin-bottom: 22px;
        }
        .info-row {
            display: flex; gap: 12px; padding: 8px 0;
            border-bottom: 1px solid #f1f5f9; font-size: .87rem;
            align-items: flex-start;
        }
        .info-row:last-child { border-bottom: none; padding-bottom: 0; }
        .info-row:first-child { padding-top: 0; }
        .info-label { width: 120px; flex-shrink: 0; color: #64748b; font-weight: 600; font-size: .8rem; padding-top: 1px; }
        .info-value { color: #0f172a; font-weight: 500; line-height: 1.45; }

        /* ── Progress stepper ── */
        .stepper-wrap {
            background: #f8fafc; border: 1px solid #e2e8f0;
            border-radius: 14px; padding: 18px 20px; margin-bottom: 22px;
        }
        .stepper-title {
            font-size: .7rem; font-weight: 700; color: #94a3b8;
            text-transform: uppercase; letter-spacing: .8px; margin-bottom: 16px;
        }
        .stepper { display: flex; gap: 0; position: relative; }
        .step { flex: 1; text-align: center; position: relative; }
        .step::before {
            content: ''; position: absolute;
            top: 15px; left: calc(-50% + 15px); right: calc(50% + 15px);
            height: 3px; background: #e2e8f0; z-index: 0;
        }
        .step:first-child::before { display: none; }
        .step.done::before   { background: var(--green); }
        .step.active::before { background: linear-gradient(to right, var(--green), #3b82f6); }
        .step-dot {
            width: 30px; height: 30px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: .72rem; font-weight: 800;
            margin: 0 auto 7px; position: relative; z-index: 1;
        }
        .step.done   .step-dot { background: var(--green); color: #fff; }
        .step.active .step-dot { background: #3b82f6; color: #fff; box-shadow: 0 0 0 5px rgba(59,130,246,.18); }
        .step.pending .step-dot{ background: #f1f5f9; color: #94a3b8; border: 2px solid #e2e8f0; }
        .step-label { font-size: .65rem; color: #94a3b8; line-height: 1.3; font-weight: 600; }
        .step.done .step-label   { color: var(--green); }
        .step.active .step-label { color: #3b82f6; font-weight: 800; }

        .current-status-bar {
            margin-top: 14px; padding: 10px 14px; border-radius: 10px;
            display: flex; align-items: center; gap: 10px; font-size: .82rem;
        }
        .current-status-bar.active { background: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8; }
        .current-status-bar.done   { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
        .current-status-bar.rejected { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }

        /* ── LOA section ── */
        .loa-section {
            border-radius: 14px; padding: 20px 22px;
            border: 2px solid;
        }
        .loa-section.available {
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
            border-color: #86efac;
        }
        .loa-section.locked {
            background: #f8fafc; border-color: #e2e8f0;
        }
        .loa-head {
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 14px;
        }
        .loa-icon {
            width: 40px; height: 40px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
        }
        .loa-section.available .loa-icon { background: #dcfce7; color: var(--green); }
        .loa-section.locked    .loa-icon { background: #f1f5f9; color: #94a3b8; }
        .loa-head-text h3 { font-size: .9rem; font-weight: 800; margin-bottom: 2px; }
        .loa-section.available .loa-head-text h3 { color: #15803d; }
        .loa-section.locked    .loa-head-text h3 { color: #94a3b8; }
        .loa-head-text p { font-size: .75rem; color: #94a3b8; }
        .loa-controls { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 14px; }
        .loa-date-wrap label { font-size: .72rem; font-weight: 700; color: #64748b; display: block; margin-bottom: 5px; }
        .loa-date-input {
            border: 1.5px solid #d1d5db; border-radius: 10px;
            padding: 9px 13px; font-size: .85rem; color: #0f172a;
            background: #fff; outline: none; transition: border-color .2s;
        }
        .loa-date-input:focus { border-color: var(--green); box-shadow: 0 0 0 3px rgba(22,163,74,.12); }
        .btn-loa {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            color: #fff; border: none; border-radius: 10px;
            padding: 11px 22px; font-weight: 700; font-size: .9rem;
            display: inline-flex; align-items: center; gap: 8px;
            text-decoration: none; cursor: pointer;
            box-shadow: 0 4px 14px rgba(22,163,74,.3);
            transition: opacity .2s, transform .15s;
        }
        .btn-loa:hover { opacity: .9; transform: translateY(-1px); color: #fff; }
        .loa-locked-msg { font-size: .83rem; color: #94a3b8; }
        .loa-locked-msg strong { color: #64748b; }
        .loa-progress-steps { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 12px; }
        .loa-step-chip {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: .7rem; font-weight: 600; padding: 4px 10px; border-radius: 20px;
        }
        .loa-step-chip.done    { background: #dcfce7; color: #15803d; }
        .loa-step-chip.pending { background: #f1f5f9; color: #94a3b8; }
        .loa-step-chip.active  { background: #dbeafe; color: #1d4ed8; }

        /* ── Footer ── */
        .portal-footer {
            text-align: center; margin-top: 28px;
            display: flex; justify-content: center; flex-wrap: wrap; gap: 6px 20px;
        }
        .portal-footer a {
            color: rgba(255,255,255,.6); font-size: .8rem; text-decoration: none;
            display: inline-flex; align-items: center; gap: 5px;
            transition: color .15s;
        }
        .portal-footer a:hover { color: #fff; }

        /* ── Alert ── */
        .alert-error {
            background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px;
            padding: 12px 18px; color: #b91c1c; font-size: .87rem;
            display: flex; align-items: center; gap: 10px; margin-bottom: 4px;
        }

        /* ── Rejected state ── */
        .rejected-banner {
            background: linear-gradient(135deg, #fef2f2, #fff5f5);
            border: 2px solid #fecaca; border-radius: 14px;
            padding: 20px 22px; text-align: center;
        }
    </style>
</head>
<body>
<div class="page-wrap">

    {{-- ── Top nav ── --}}
    <nav class="top-nav">
        <a href="{{ route('tracking.index') }}" class="brand">
            <div class="brand-icon"><i class="bi bi-journal-bookmark-fill"></i></div>
            <div>
                <span class="brand-name">SIPERA</span>
                <span class="brand-sub">Portal Penulis</span>
            </div>
        </a>
        <div class="nav-links">
            <a href="{{ route('login') }}" class="nav-link-btn">
                <i class="bi bi-box-arrow-in-right me-1"></i>Login
            </a>
        </div>
    </nav>

    {{-- ── Hero ── --}}
    @unless(isset($submission))
    <div class="hero">
        <div class="hero-badge">
            <i class="bi bi-shield-check-fill"></i>
            Portal Resmi SIPERA
        </div>
        <h1>Lacak Status & Unduh <span>LOA</span></h1>
        <p>Pantau progress artikel dan akses Letter of Acceptance — cukup masukkan Kode SIPERA Anda di satu tempat.</p>
    </div>
    @endunless

    @isset($submission)
    <div style="padding-top: 24px;"></div>
    @endisset

    {{-- ── Search card ── --}}
    <div class="search-card">
        <div class="search-card-body">

            @if($errors->any())
            <div class="alert-error mb-4">
                <i class="bi bi-exclamation-triangle-fill" style="font-size:1.1rem;flex-shrink:0;"></i>
                <span>{{ $errors->first() }}</span>
            </div>
            @endif

            <form action="{{ route('tracking.search') }}" method="POST" id="searchForm">
                @csrf
                <div class="search-label">
                    <i class="bi bi-upc-scan"></i>
                    Kode Submission SIPERA
                </div>
                <div class="search-input-wrap">
                    <input type="text"
                           name="kode_loa"
                           id="kodeInput"
                           class="kode-input"
                           placeholder="Contoh: SUB2026060001"
                           value="{{ old('kode_loa', isset($submission) ? $submission->kode_submit : '') }}"
                           autocomplete="off"
                           autofocus>
                    <button type="submit" class="btn-cari" id="btnCari">
                        <i class="bi bi-search"></i>
                        <span>Cek Sekarang</span>
                    </button>
                </div>
                <div class="search-hint">
                    <i class="bi bi-info-circle"></i>
                    Kode ada di email konfirmasi — format SUB atau JAF diikuti tahun dan nomor urut
                </div>
            </form>
        </div>

        @unless(isset($submission))
        <hr class="divider">
        <div class="feature-row">
            <div class="feature-pill">
                <div class="fp-icon" style="background:#eff6ff;color:#1d4ed8;">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div class="fp-title">Status Real-time</div>
                <div class="fp-desc">Lihat di tahap mana artikel Anda sekarang berada</div>
            </div>
            <div class="feature-pill">
                <div class="fp-icon" style="background:#f0fdf4;color:#15803d;">
                    <i class="bi bi-file-earmark-check-fill"></i>
                </div>
                <div class="fp-title">Unduh LOA</div>
                <div class="fp-desc">Akses & cetak Letter of Acceptance kapan saja</div>
            </div>
            <div class="feature-pill">
                <div class="fp-icon" style="background:#fff7ed;color:#c2410c;">
                    <i class="bi bi-calendar3"></i>
                </div>
                <div class="fp-title">Pilih Tanggal</div>
                <div class="fp-desc">Atur tanggal LOA sesuai kebutuhan dokumen Anda</div>
            </div>
        </div>
        @endunless
    </div>

    {{-- ── Result ── --}}
    @isset($submission)
    @php
        $journal   = $submission->journalSlot?->journalMaster;
        $slot      = $submission->journalSlot;
        $realStatus= $submission->getRealStatus();
        $primary   = $journal?->primary_color  ?? '#1a237e';
        $secondary = $journal?->secondary_color ?? '#8B6914';

        $stageMap = [
            'SUBMITTED'          => 1,
            'EDITOR1_PROCESS'    => 2,
            'AUTHOR1_PROCESS'    => 2,
            'EDITOR2_PROCESS'    => 2,
            'REVIEWER1_PROCESS'  => 2,
            'REVIEWER2_PROCESS'  => 2,
            'EDITOR3_PROCESS'    => 2,
            'AUTHOR2_PROCESS'    => 2,
            'PRODUCTION_PROCESS' => 3,
            'VALIDATOR_PROCESS'  => 4,
            'PUBLISHED'          => 5,
            'REJECTED'           => 0,
        ];
        $stage = $stageMap[$realStatus] ?? 1;

        $loaAvailable = $submission->production_valid || $submission->validator_valid
                        || $realStatus === 'PUBLISHED';

        $statusLabels = [
            'SUBMITTED'          => ['label' => 'Menunggu Proses',  'color' => '#f59e0b', 'icon' => 'bi-hourglass-split'],
            'EDITOR1_PROCESS'    => ['label' => 'Proses Editor 1',  'color' => '#0288d1', 'icon' => 'bi-pencil-square'],
            'AUTHOR1_PROCESS'    => ['label' => 'Proses Author 1',  'color' => '#7c3aed', 'icon' => 'bi-person-check'],
            'EDITOR2_PROCESS'    => ['label' => 'Proses Editor 2',  'color' => '#0288d1', 'icon' => 'bi-pencil-square'],
            'REVIEWER1_PROCESS'  => ['label' => 'Proses Reviewer 1','color' => '#b45309', 'icon' => 'bi-eye'],
            'REVIEWER2_PROCESS'  => ['label' => 'Proses Reviewer 2','color' => '#b45309', 'icon' => 'bi-eye'],
            'EDITOR3_PROCESS'    => ['label' => 'Proses Editor 3',  'color' => '#0288d1', 'icon' => 'bi-pencil-square'],
            'AUTHOR2_PROCESS'    => ['label' => 'Proses Author 2',  'color' => '#7c3aed', 'icon' => 'bi-person-check'],
            'PRODUCTION_PROCESS' => ['label' => 'Tahap Produksi',   'color' => '#15803d', 'icon' => 'bi-printer'],
            'VALIDATOR_PROCESS'  => ['label' => 'Validasi Akhir',   'color' => '#6d28d9', 'icon' => 'bi-shield-check'],
            'PUBLISHED'          => ['label' => 'Terbit',           'color' => '#15803d', 'icon' => 'bi-book'],
            'REJECTED'           => ['label' => 'Tidak Diterima',   'color' => '#dc2626', 'icon' => 'bi-x-circle'],
        ];
        $currentStatus = $statusLabels[$realStatus] ?? ['label' => $realStatus, 'color' => '#64748b', 'icon' => 'bi-circle'];
    @endphp

    <div class="result-card">

        {{-- Journal header --}}
        <div class="result-header" style="background:{{ $primary }};">
            @if($journal?->logo_path)
                <img src="{{ Storage::url($journal->logo_path) }}" class="journal-logo" alt="Logo">
            @else
                <div class="journal-abbr">
                    {{ strtoupper(substr($journal?->kode_singkat ?: ($journal?->nama_jurnal ?? 'S'), 0, 2)) }}
                </div>
            @endif
            <div style="flex:1;min-width:0;">
                <div class="jrn-title">{{ $journal?->nama_jurnal ?? 'Jurnal SIPERA' }}</div>
                <div class="jrn-meta">
                    @if($journal?->kode_singkat) {{ $journal->kode_singkat }} &nbsp;·&nbsp; @endif
                    @if($slot) Vol.{{ $slot->volume }}, No.{{ $slot->nomor }}, {{ $slot->bulan }} {{ $slot->tahun }} @endif
                    @if($journal?->e_issn) &nbsp;·&nbsp; E-ISSN {{ $journal->e_issn }} @endif
                </div>
            </div>
            <div class="ms-2 flex-shrink-0">
                <span class="badge rounded-pill" style="background:{{ $currentStatus['color'] }};font-size:.72rem;padding:5px 12px;">
                    <i class="bi {{ $currentStatus['icon'] }} me-1"></i>{{ $currentStatus['label'] }}
                </span>
            </div>
        </div>

        <div class="result-body">

            {{-- Article info --}}
            <div class="article-info">
                <div class="info-row">
                    <div class="info-label"><i class="bi bi-file-text me-1"></i>Judul</div>
                    <div class="info-value fw-semibold">{{ $submission->judul_artikel }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="bi bi-person me-1"></i>Penulis</div>
                    <div class="info-value">{{ $submission->nama_penulis }}</div>
                </div>
                @if($submission->affiliation_penulis)
                <div class="info-row">
                    <div class="info-label"><i class="bi bi-building me-1"></i>Afiliasi</div>
                    <div class="info-value">{{ $submission->affiliation_penulis }}</div>
                </div>
                @endif
                <div class="info-row">
                    <div class="info-label"><i class="bi bi-upc me-1"></i>Kode Submit</div>
                    <div class="info-value">
                        <code style="background:#f1f5f9;padding:2px 8px;border-radius:6px;font-size:.88rem;color:#1a237e;font-weight:700;">
                            {{ $submission->kode_submit }}
                        </code>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="bi bi-calendar3 me-1"></i>Tgl Submit</div>
                    <div class="info-value">
                        {{ $submission->tanggal_submit ? \Carbon\Carbon::parse($submission->tanggal_submit)->isoFormat('D MMMM YYYY') : '—' }}
                    </div>
                </div>
                @if($submission->link_publish)
                <div class="info-row">
                    <div class="info-label"><i class="bi bi-globe me-1"></i>Link Terbit</div>
                    <div class="info-value">
                        <a href="{{ $submission->link_publish }}" target="_blank"
                           style="color:#1a237e;text-decoration:none;font-weight:600;">
                            <i class="bi bi-box-arrow-up-right me-1"></i>Lihat Artikel
                        </a>
                    </div>
                </div>
                @endif
            </div>

            {{-- Progress stepper --}}
            @if($realStatus !== 'REJECTED')
            <div class="stepper-wrap">
                <div class="stepper-title"><i class="bi bi-diagram-3 me-1"></i>Progress Artikel</div>
                <div class="stepper">
                    @php
                        $steps = [
                            ['label' => 'Submit',    'icon' => 'bi-upload'],
                            ['label' => 'Review',    'icon' => 'bi-eye'],
                            ['label' => 'Produksi',  'icon' => 'bi-printer'],
                            ['label' => 'Validasi',  'icon' => 'bi-shield-check'],
                            ['label' => 'Terbit',    'icon' => 'bi-book'],
                        ];
                    @endphp
                    @foreach($steps as $idx => $step)
                    @php $n = $idx + 1; $cls = $n < $stage ? 'done' : ($n == $stage ? 'active' : 'pending'); @endphp
                    <div class="step {{ $cls }}">
                        <div class="step-dot">
                            @if($cls === 'done') <i class="bi bi-check-lg"></i>
                            @elseif($cls === 'active') <i class="bi {{ $step['icon'] }}"></i>
                            @else {{ $n }} @endif
                        </div>
                        <div class="step-label">{{ $step['label'] }}</div>
                    </div>
                    @endforeach
                </div>
                <div class="current-status-bar {{ $realStatus === 'PUBLISHED' ? 'done' : 'active' }}">
                    <i class="bi {{ $currentStatus['icon'] }}" style="font-size:1rem;flex-shrink:0;"></i>
                    <span>
                        Status saat ini: <strong>{{ $currentStatus['label'] }}</strong>
                        @if($realStatus === 'PUBLISHED') — Selamat, artikel Anda telah terbit! 🎉
                        @elseif($loaAvailable) — Artikel diterima, LOA tersedia
                        @endif
                    </span>
                </div>
            </div>
            @else
            <div class="rejected-banner mb-4">
                <i class="bi bi-x-circle-fill" style="font-size:2rem;color:#dc2626;"></i>
                <div style="font-weight:700;color:#991b1b;margin-top:8px;">Artikel Tidak Dilanjutkan</div>
                <div style="font-size:.85rem;color:#b91c1c;margin-top:4px;">Mohon maaf, artikel ini tidak dapat dilanjutkan ke tahap berikutnya.</div>
            </div>
            @endif

            {{-- LOA section --}}
            <div class="loa-section {{ $loaAvailable ? 'available' : 'locked' }}">
                <div class="loa-head">
                    <div class="loa-icon">
                        <i class="bi bi-file-earmark-check{{ $loaAvailable ? '-fill' : '' }}"></i>
                    </div>
                    <div class="loa-head-text">
                        <h3>{{ $loaAvailable ? 'Letter of Acceptance Tersedia' : 'Letter of Acceptance' }}</h3>
                        <p>{{ $loaAvailable ? 'Dokumen LOA siap diunduh & dicetak' : 'Akan tersedia setelah tahap Produksi selesai' }}</p>
                    </div>
                    @if($loaAvailable)
                    <div class="ms-auto">
                        <span class="badge" style="background:#dcfce7;color:#15803d;font-size:.72rem;padding:5px 10px;border-radius:20px;">
                            <i class="bi bi-check-circle-fill me-1"></i>Diterima
                        </span>
                    </div>
                    @endif
                </div>

                @if($loaAvailable)
                <div class="loa-controls">
                    <div class="loa-date-wrap">
                        <label>Tanggal LOA <span style="font-weight:400;color:#94a3b8;">(default: hari ini)</span></label>
                        <input type="date" id="loaDate" class="loa-date-input"
                               value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <a id="btnLoa"
                       href="{{ route('loa.public', ['kode_loa' => $submission->kode_loa]) }}?tanggal={{ now()->format('Y-m-d') }}"
                       class="btn-loa" target="_blank">
                        <i class="bi bi-file-earmark-arrow-down"></i>
                        Buka & Cetak LOA
                    </a>
                </div>
                <div style="margin-top:12px;font-size:.73rem;color:#6b7280;display:flex;align-items:center;gap:6px;">
                    <i class="bi bi-shield-check" style="color:#16a34a;"></i>
                    Dokumen dilengkapi QR Code verifikasi & watermark resmi SIPERA
                </div>
                @else
                <div class="loa-locked-msg">
                    Saat ini artikel Anda sedang dalam tahap <strong>{{ $currentStatus['label'] }}</strong>.
                    LOA akan otomatis tersedia begitu tahap Produksi selesai divalidasi.
                </div>
                <div class="loa-progress-steps">
                    <span class="loa-step-chip {{ $stage > 1 ? 'done' : 'active' }}">
                        <i class="bi {{ $stage > 1 ? 'bi-check-lg' : 'bi-circle-fill' }}"></i> Submit
                    </span>
                    <span class="loa-step-chip {{ $stage > 2 ? 'done' : ($stage == 2 ? 'active' : 'pending') }}">
                        <i class="bi {{ $stage > 2 ? 'bi-check-lg' : ($stage == 2 ? 'bi-circle-fill' : 'bi-circle') }}"></i> Review
                    </span>
                    <span class="loa-step-chip {{ $stage > 3 ? 'done' : ($stage == 3 ? 'active' : 'pending') }}">
                        <i class="bi {{ $stage > 3 ? 'bi-check-lg' : ($stage == 3 ? 'bi-circle-fill' : 'bi-circle') }}"></i> Produksi
                        @if($stage < 3) <span style="font-size:.65rem;">(LOA tersedia di sini)</span> @endif
                    </span>
                </div>
                @endif
            </div>

        </div>
    </div>
    @endisset

    {{-- ── Footer ── --}}
    <div class="portal-footer">
        <a href="mailto:admin@apji.org"><i class="bi bi-envelope"></i>Bantuan</a>
        <span style="color:rgba(255,255,255,.35);font-size:.75rem;">SIPERA &copy; {{ date('Y') }}</span>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    // Auto-uppercase kode input
    var inp = document.getElementById('kodeInput');
    if (inp) {
        inp.addEventListener('input', function () {
            var p = this.selectionStart;
            this.value = this.value.toUpperCase();
            this.setSelectionRange(p, p);
        });
    }

    // LOA date picker → update button href
    var datePick = document.getElementById('loaDate');
    var btnLoa   = document.getElementById('btnLoa');
    if (datePick && btnLoa) {
        var base = btnLoa.href.split('?')[0];
        datePick.addEventListener('change', function () {
            btnLoa.href = base + (this.value ? '?tanggal=' + this.value : '');
        });
    }

    // Loading state
    var form = document.getElementById('searchForm');
    var btn  = document.getElementById('btnCari');
    if (form && btn) {
        form.addEventListener('submit', function () {
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> <span>Mencari…</span>';
            btn.disabled = true;
        });
    }
})();
</script>
</body>
</html>
