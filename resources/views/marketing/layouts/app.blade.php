<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>(function(){var t=localStorage.getItem('mktTheme');if(t==='dark-sidebar')document.documentElement.setAttribute('data-theme','dark-sidebar');if(localStorage.getItem('mktSidebarCollapsed')==='1')document.documentElement.setAttribute('data-sidebar','collapsed');})()</script>
    <title>@yield('title', 'Marketing Dashboard') - REVANA</title>
    @if(isset($appSettings['favicon']) && $appSettings['favicon'])
    <link rel="icon" href="{{ asset('storage/' . $appSettings['favicon']) }}" type="image/x-icon">
    @endif
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar-marketing {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        .sidebar {
            background: white;
            min-height: calc(100vh - 56px);
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            padding: 20px 0;
        }
        .sidebar .nav-link {
            color: #333;
            padding: 12px 20px;
            border-left: 3px solid transparent;
        }
        .sidebar .nav-link:hover {
            background: #f8f9fa;
            border-left-color: #11998e;
        }
        .sidebar .nav-link.active {
            background: #e8f5e9;
            border-left-color: #11998e;
            color: #11998e;
            font-weight: 600;
        }
        /* Collapsible section headers */
        .sidebar-sec-toggle {
            width: 100%;
            background: none;
            border: none;
            border-radius: 0;
            text-align: left;
            cursor: pointer;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            padding: 10px 20px 6px;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.15s;
        }
        .sidebar-sec-toggle:hover { background: #f0f8f5; }
        .sidebar-sec-toggle .sec-chevron {
            margin-left: auto;
            font-size: 0.7rem;
            opacity: 0.55;
            transition: transform 0.2s ease;
        }
        .sidebar-sec-toggle:not(.collapsed) .sec-chevron {
            transform: rotate(180deg);
        }
        .sidebar .nav-link-sub {
            padding-left: 32px;
            font-size: 0.875rem;
        }
        .content {
            padding: 30px;
        }
        .card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        .stat-card {
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-3px);
        }
        .points-badge {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: bold;
        }

        /* ===== DARK SIDEBAR THEME (Marketing) ===== */
        html[data-theme="dark-sidebar"] .navbar-marketing {
            background: #0f172a !important;
        }
        html[data-theme="dark-sidebar"] .sidebar {
            background: #1e293b;
            box-shadow: 1px 0 0 rgba(255,255,255,0.06);
        }
        html[data-theme="dark-sidebar"] .sidebar .nav-link {
            color: #94a3b8;
            border-left-color: transparent;
        }
        html[data-theme="dark-sidebar"] .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.05);
            border-left-color: #6366f1;
            color: #e2e8f0;
        }
        html[data-theme="dark-sidebar"] .sidebar .nav-link.active {
            background: rgba(99,102,241,0.13);
            border-left-color: #6366f1;
            color: #a5b4fc;
            font-weight: 600;
        }
        html[data-theme="dark-sidebar"] .sidebar-sec-toggle {
            color: #475569 !important;
        }
        html[data-theme="dark-sidebar"] .sidebar-sec-toggle:hover {
            background: rgba(255,255,255,0.04) !important;
        }
        html[data-theme="dark-sidebar"] .sidebar hr {
            border-color: rgba(255,255,255,0.08);
            opacity: 1;
        }

        /* Sidebar toggle */
        .sidebar {
            transition: max-width 0.25s ease, padding 0.25s ease, opacity 0.2s ease;
            max-width: 16.6667%;
        }
        html[data-sidebar="collapsed"] .sidebar,
        .sidebar.sidebar-collapsed {
            max-width: 0 !important;
            flex: 0 0 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
            min-height: 0 !important;
        }
        html[data-sidebar="collapsed"] .col-content,
        .col-content.sidebar-expanded {
            flex: 0 0 100%;
            max-width: 100%;
        }
        #mktSidebarBtn {
            color: rgba(255,255,255,0.8);
            background: transparent;
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 1rem;
            line-height: 1;
            cursor: pointer;
            transition: all 0.2s;
        }
        #mktSidebarBtn:hover {
            color: #fff;
            border-color: rgba(255,255,255,0.6);
            background: rgba(255,255,255,0.1);
        }
        /* Tombol toggle tema */
        #mktThemeBtn {
            color: rgba(255,255,255,0.75);
            background: transparent;
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 0.8rem;
            line-height: 1;
            transition: all 0.2s;
            cursor: pointer;
        }
        #mktThemeBtn:hover {
            color: #fff;
            border-color: rgba(255,255,255,0.6);
            background: rgba(255,255,255,0.08);
        }
    </style>
</head>
<body>
    @if(session('admin_impersonating'))
    <div class="bg-warning text-dark py-1 px-3 d-flex align-items-center gap-2" style="font-size:.85rem; border-bottom: 2px solid #ffc107;">
        <i class="bi bi-eye-fill"></i>
        <strong>Mode Admin:</strong> Anda sedang melihat sebagai <strong>{{ auth()->guard('marketing')->user()->name }}</strong>
        <form method="POST" action="{{ route('admin.marketings.return-to-admin') }}" class="ms-auto mb-0">
            @csrf
            <button type="submit" class="btn btn-dark btn-sm fw-bold">
                <i class="bi bi-box-arrow-left me-1"></i>Kembali ke Admin
            </button>
        </form>
    </div>
    @endif

    <nav class="navbar navbar-expand-lg navbar-dark navbar-marketing">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('marketing.dashboard') }}">
                <i class="bi bi-megaphone-fill"></i> Marketing Portal
            </a>
            @if(session('admin_impersonating'))
            <span class="badge bg-warning text-dark ms-2 d-flex align-items-center gap-1 flex-shrink-0" style="font-size:.72rem;">
                <i class="bi bi-eye-fill"></i>
                <span class="d-none d-sm-inline">Mode Admin</span>
            </span>
            @endif
            <div class="navbar-nav ms-auto align-items-center">
                <li class="nav-item me-1">
                    <button id="mktSidebarBtn" onclick="toggleSidebar()" title="Sembunyikan/Tampilkan Menu">
                        <i class="bi bi-layout-sidebar" id="mktSidebarIcon"></i>
                    </button>
                </li>
                <span class="nav-link d-flex align-items-center gap-2">
                    <span class="points-badge">
                        @php
                            $mktId = auth()->guard('marketing')->id();
                            $mktPoints = \Illuminate\Support\Facades\Cache::remember("marketing.point_count.{$mktId}", 120, fn() =>
                                auth()->guard('marketing')->user()->submissions()->count()
                            );
                        @endphp
                        <i class="bi bi-star-fill"></i> {{ $mktPoints }} Point
                    </span>
                    <a href="{{ route('marketing.refresh-points') }}" class="btn btn-sm btn-light rounded-circle" title="Refresh Point" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-arrow-clockwise text-success"></i>
                    </a>
                </span>
                <li class="nav-item d-flex align-items-center me-1">
                    <button id="mktThemeBtn" onclick="toggleTheme()" title="">
                        <i class="bi bi-moon-stars-fill" id="mktThemeIcon"></i>
                        <span id="mktThemeLabel" class="d-none d-lg-inline ms-1"></span>
                    </button>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> {{ auth()->guard('marketing')->user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a href="{{ route('marketing.refresh-points') }}" class="dropdown-item">
                                <i class="bi bi-arrow-repeat text-warning"></i> Refresh Point
                            </a>
                        </li>
                        @if(session('admin_impersonating'))
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('admin.marketings.return-to-admin') }}" class="px-1 py-1">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm w-100 fw-bold">
                                    <i class="bi bi-box-arrow-left me-1"></i>Kembali ke Admin
                                </button>
                            </form>
                        </li>
                        @endif
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#mktLogoutModal">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </button>
                        </li>
                    </ul>
                </li>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar">
                @php
                    $currentRoute = Route::currentRouteName();
                    $mktNormalActive  = str_contains($currentRoute, 'marketing.submissions') && !request('program') && !str_contains($currentRoute, 'create');
                    $mktFtActive      = str_contains($currentRoute, 'marketing.fasttrack');
                    $mktBkdActive     = request('program') === 'bkd';
                    $mktJafaActive    = request('program') === 'jafa';
                    $mktJournalActive = str_contains($currentRoute, 'marketing.journals') || str_contains($currentRoute, 'marketing.journal-slots');
                    $mktLoaMasterActive = str_contains($currentRoute, 'marketing.loa-master');
                @endphp

                {{-- Dashboard --}}
                <a href="{{ route('marketing.dashboard') }}" class="nav-link {{ $currentRoute == 'marketing.dashboard' ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>

                {{-- ===== PENGELOLAAN NORMAL (collapsible) ===== --}}
                @rolecap('marketing', 'buat_submission')
                <button class="sidebar-sec-toggle {{ $mktNormalActive ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#sec-mkt-normal"
                        aria-expanded="{{ $mktNormalActive ? 'true' : 'false' }}"
                        style="color:#6f42c1;">
                    <i class="bi bi-journal-bookmark-fill" style="color:#6f42c1;opacity:.8;"></i>
                    <span>Pengelolaan Normal</span>
                    <i class="bi bi-chevron-down sec-chevron"></i>
                </button>
                <div class="collapse {{ $mktNormalActive ? 'show' : '' }}" id="sec-mkt-normal">
                    <a href="{{ route('marketing.submissions') }}"
                       class="nav-link nav-link-sub {{ str_contains($currentRoute, 'marketing.submissions') && !str_contains($currentRoute, 'monitoring') && !str_contains($currentRoute, 'create') && !request('program') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text"></i> Artikel
                    </a>
                    <a href="{{ route('marketing.submissions.monitoring') }}"
                       class="nav-link nav-link-sub {{ str_contains($currentRoute, 'marketing.submissions.monitoring') && !request('program') ? 'active' : '' }}">
                        <i class="bi bi-bar-chart-line"></i> Monitoring Artikel
                    </a>
                </div>
                @endrolecap

                {{-- ===== PENGELOLAAN FASTTRACK (collapsible) ===== --}}
                @feature('fasttrack')
                @rolecap('marketing', 'fasttrack')
                <button class="sidebar-sec-toggle {{ $mktFtActive ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#sec-mkt-ft"
                        aria-expanded="{{ $mktFtActive ? 'true' : 'false' }}"
                        style="color:#d97706;">
                    <i class="bi bi-lightning-charge-fill text-warning"></i>
                    <span>Pengelolaan Fasttrack</span>
                    <i class="bi bi-chevron-down sec-chevron"></i>
                </button>
                <div class="collapse {{ $mktFtActive ? 'show' : '' }}" id="sec-mkt-ft">
                    <a href="{{ route('marketing.fasttrack.create') }}"
                       class="nav-link nav-link-sub {{ $currentRoute == 'marketing.fasttrack.create' ? 'active' : '' }}">
                        <i class="bi bi-plus-circle-fill text-warning"></i> Input Fasttrack
                    </a>
                    <a href="{{ route('marketing.fasttrack.index') }}"
                       class="nav-link nav-link-sub {{ str_contains($currentRoute, 'marketing.fasttrack') && !str_contains($currentRoute, 'monitoring') && !str_contains($currentRoute, 'create') ? 'active' : '' }}">
                        <i class="bi bi-lightning-charge text-warning"></i> Data Fasttrack
                    </a>
                    <a href="{{ route('marketing.fasttrack.monitoring') }}"
                       class="nav-link nav-link-sub {{ str_contains($currentRoute, 'marketing.fasttrack.monitoring') ? 'active' : '' }}">
                        <i class="bi bi-bar-chart text-warning"></i> Monitoring Fasttrack
                    </a>
                </div>
                @endrolecap
                @endfeature

                {{-- ===== PENGELOLAAN BKD (collapsible) ===== --}}
                <button class="sidebar-sec-toggle {{ $mktBkdActive ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#sec-mkt-bkd"
                        aria-expanded="{{ $mktBkdActive ? 'true' : 'false' }}"
                        style="color:#0dcaf0;">
                    <i class="bi bi-briefcase-fill text-info"></i>
                    <span>Pengelolaan BKD</span>
                    <i class="bi bi-chevron-down sec-chevron"></i>
                </button>
                <div class="collapse {{ $mktBkdActive ? 'show' : '' }}" id="sec-mkt-bkd">
                    <a href="{{ route('marketing.submissions.create', ['program' => 'bkd']) }}"
                       class="nav-link nav-link-sub {{ $currentRoute == 'marketing.submissions.create' && request('program') === 'bkd' ? 'active' : '' }}">
                        <i class="bi bi-plus-circle-fill text-info"></i> Input Data BKD
                    </a>
                    <a href="{{ route('marketing.submissions', ['program' => 'bkd']) }}"
                       class="nav-link nav-link-sub {{ str_contains($currentRoute, 'marketing.submissions') && !str_contains($currentRoute, 'monitoring') && !str_contains($currentRoute, 'create') && request('program') === 'bkd' ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text text-info"></i> Data Submit BKD
                    </a>
                    <a href="{{ route('marketing.submissions.monitoring', ['program' => 'bkd']) }}"
                       class="nav-link nav-link-sub {{ str_contains($currentRoute, 'marketing.submissions.monitoring') && request('program') === 'bkd' ? 'active' : '' }}">
                        <i class="bi bi-bar-chart-line text-info"></i> Monitoring Proses BKD
                    </a>
                </div>

                {{-- ===== PENGELOLAAN JAFA (collapsible) ===== --}}
                <button class="sidebar-sec-toggle {{ $mktJafaActive ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#sec-mkt-jafa"
                        aria-expanded="{{ $mktJafaActive ? 'true' : 'false' }}"
                        style="color:#198754;">
                    <i class="bi bi-folder2-open text-success"></i>
                    <span>Pengelolaan JAFA</span>
                    <i class="bi bi-chevron-down sec-chevron"></i>
                </button>
                <div class="collapse {{ $mktJafaActive ? 'show' : '' }}" id="sec-mkt-jafa">
                    <a href="{{ route('marketing.submissions.create', ['program' => 'jafa']) }}"
                       class="nav-link nav-link-sub {{ $currentRoute == 'marketing.submissions.create' && request('program') === 'jafa' ? 'active' : '' }}">
                        <i class="bi bi-plus-circle-fill text-success"></i> Input Data JAFA
                    </a>
                    <a href="{{ route('marketing.submissions', ['program' => 'jafa']) }}"
                       class="nav-link nav-link-sub {{ str_contains($currentRoute, 'marketing.submissions') && !str_contains($currentRoute, 'monitoring') && !str_contains($currentRoute, 'create') && request('program') === 'jafa' ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text text-success"></i> Data Submit JAFA
                    </a>
                    <a href="{{ route('marketing.submissions.monitoring', ['program' => 'jafa']) }}"
                       class="nav-link nav-link-sub {{ str_contains($currentRoute, 'marketing.submissions.monitoring') && request('program') === 'jafa' ? 'active' : '' }}">
                        <i class="bi bi-bar-chart-line text-success"></i> Monitoring Proses JAFA
                    </a>
                </div>

                {{-- ===== PENGELOLAAN JURNAL (collapsible) ===== --}}
                <button class="sidebar-sec-toggle {{ $mktJournalActive ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#sec-mkt-journal"
                        aria-expanded="{{ $mktJournalActive ? 'true' : 'false' }}"
                        style="color:#6c757d;">
                    <i class="bi bi-journal-bookmark"></i>
                    <span>Pengelolaan Jurnal</span>
                    <i class="bi bi-chevron-down sec-chevron"></i>
                </button>
                <div class="collapse {{ $mktJournalActive ? 'show' : '' }}" id="sec-mkt-journal">
                    @rolecap('marketing', 'kelola_jurnal')
                    <a href="{{ route('marketing.journals.index') }}"
                       class="nav-link nav-link-sub {{ str_contains($currentRoute, 'marketing.journals') ? 'active' : '' }}">
                        <i class="bi bi-journal-text"></i> Data Jurnal
                    </a>
                    @endrolecap
                    @rolecap('marketing', 'kelola_slot')
                    <a href="{{ route('marketing.journal-slots.index') }}"
                       class="nav-link nav-link-sub {{ str_contains($currentRoute, 'marketing.journal-slots') ? 'active' : '' }}">
                        <i class="bi bi-calendar3"></i> Data Slot
                    </a>
                    @endrolecap
                </div>

                <hr style="margin: 12px 0; opacity: 0.2;">

                @feature('points')
                @rolecap('marketing', 'points')
                <a href="{{ route('marketing.points') }}" class="nav-link {{ $currentRoute == 'marketing.points' ? 'active' : '' }}">
                    <i class="bi bi-trophy"></i> Point Saya
                </a>
                <a href="{{ route('marketing.points.rankings') }}" class="nav-link {{ $currentRoute == 'marketing.points.rankings' ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-fill text-warning"></i> Peringkat Point
                </a>
                @endrolecap
                @endfeature

                <a href="{{ route('marketing.reports.journal-articles') }}" class="nav-link {{ str_contains($currentRoute, 'marketing.reports') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-bar-graph"></i> Laporan Jurnal
                </a>

                <a href="{{ route('marketing.loa-master.index') }}" class="nav-link {{ $mktLoaMasterActive ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-check-fill text-success"></i> Master LOA
                </a>

                <hr style="margin: 12px 0; opacity: 0.2;">

                <a href="{{ route('marketing.profile.edit') }}" class="nav-link {{ str_contains($currentRoute, 'marketing.profile') ? 'active' : '' }}">
                    <i class="bi bi-person-circle"></i> Profile Saya
                </a>
            </div>
            <div class="col-md-10 col-content content">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif
                
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif
                
                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Marketing Logout Modal -->
    <div class="modal fade" id="mktLogoutModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning text-dark border-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-arrow-repeat me-2"></i> Sebelum Logout
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4 text-center">
                    <i class="bi bi-star-fill text-warning" style="font-size:3rem"></i>
                    <p class="fw-semibold mt-3 mb-1">Refresh point sebelum logout?</p>
                    <p class="text-muted small mb-0">Pastikan total point marketing Anda sudah tersinkronisasi dengan data submission terkini.</p>
                </div>
                <div class="modal-footer border-0 justify-content-center gap-2 pb-4">
                    <a href="{{ route('marketing.refresh-points') }}?logout=1" class="btn btn-warning fw-semibold px-4">
                        <i class="bi bi-arrow-repeat me-1"></i> Refresh & Logout
                    </a>
                    <form method="POST" action="{{ route('marketing.logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger px-4">
                            <i class="bi bi-box-arrow-right me-1"></i> Logout Saja
                        </button>
                    </form>
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Batal</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Pengumuman fitur tema (one-time) -->
    <div class="modal fade" id="themeAnnouncementModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
            <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">
                <div class="modal-header border-0 pb-0" style="background:linear-gradient(135deg,#1e293b 0%,#334155 100%);">
                    <div class="w-100 text-center py-3">
                        <div style="font-size:2.8rem;">🎨</div>
                        <h5 class="text-white fw-bold mt-2 mb-0">Fitur Baru: Pilih Tema</h5>
                    </div>
                </div>
                <div class="modal-body text-center px-4 py-3">
                    <p class="text-muted mb-3">Sekarang Anda bisa memilih tampilan yang paling nyaman.</p>
                    <div class="d-flex justify-content-center gap-3 mb-3">
                        <div class="p-3 rounded text-center flex-fill" style="background:#f8fafc;border:2px solid #e2e8f0;">
                            <div style="font-size:1.6rem;">☀️</div>
                            <div class="small fw-semibold mt-1">Tema Terang</div>
                            <div class="small text-muted">Default seperti biasa</div>
                        </div>
                        <div class="p-3 rounded text-center flex-fill" style="background:#1e293b;border:2px solid #334155;">
                            <div style="font-size:1.6rem;">🌙</div>
                            <div class="small fw-semibold mt-1 text-white">Tema Gelap</div>
                            <div class="small" style="color:#94a3b8;">Sidebar lebih elegan</div>
                        </div>
                    </div>
                    <p class="text-muted small mb-0">
                        Klik ikon <strong>🌙</strong> di pojok kanan atas navbar untuk berganti tema kapan saja.
                    </p>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center pb-4">
                    <button type="button" class="btn btn-primary px-5 fw-semibold" data-bs-dismiss="modal">
                        Oke, Mengerti!
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function toggleSidebar() {
        var sidebar = document.querySelector('.sidebar');
        var content = document.querySelector('.col-content');
        var isCollapsed = sidebar.classList.toggle('sidebar-collapsed');
        if (content) content.classList.toggle('sidebar-expanded', isCollapsed);
        localStorage.setItem('mktSidebarCollapsed', isCollapsed ? '1' : '0');
        document.documentElement.setAttribute('data-sidebar', isCollapsed ? 'collapsed' : 'visible');
        var icon = document.getElementById('mktSidebarIcon');
        if (icon) icon.className = isCollapsed ? 'bi bi-layout-sidebar-inset' : 'bi bi-layout-sidebar';
    }
    function applyTheme(theme) {
        var isDark = theme === 'dark-sidebar';
        document.documentElement.setAttribute('data-theme', isDark ? 'dark-sidebar' : 'default');
        var icon  = document.getElementById('mktThemeIcon');
        var label = document.getElementById('mktThemeLabel');
        var btn   = document.getElementById('mktThemeBtn');
        if (icon)  icon.className = isDark ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
        if (label) label.textContent = isDark ? 'Tema Terang' : 'Tema Gelap';
        if (btn)   btn.title = isDark ? 'Kembali ke tema terang' : 'Coba tema gelap sidebar';
    }
    function toggleTheme() {
        var isDark = document.documentElement.getAttribute('data-theme') === 'dark-sidebar';
        var next = isDark ? 'default' : 'dark-sidebar';
        localStorage.setItem('mktTheme', next);
        applyTheme(next);
    }
    document.addEventListener('DOMContentLoaded', function() {
        // Apply sidebar state
        var sidebarCollapsed = localStorage.getItem('mktSidebarCollapsed') === '1';
        var sidebar = document.querySelector('.sidebar');
        var content = document.querySelector('.col-content');
        var icon = document.getElementById('mktSidebarIcon');
        if (sidebarCollapsed && sidebar) {
            sidebar.classList.add('sidebar-collapsed');
            if (content) content.classList.add('sidebar-expanded');
        }
        if (icon) icon.className = sidebarCollapsed ? 'bi bi-layout-sidebar-inset' : 'bi bi-layout-sidebar';

        var saved = localStorage.getItem('mktTheme') || 'default';
        applyTheme(saved);

        if (!localStorage.getItem('themeSwitcherNotified')) {
            var el = document.getElementById('themeAnnouncementModal');
            if (el) {
                el.addEventListener('hidden.bs.modal', function() {
                    localStorage.setItem('themeSwitcherNotified', '1');
                });
                bootstrap.Modal.getOrCreateInstance(el).show();
            }
        }
    });
    </script>
    @include('partials.drag-to-scroll')
    @yield('scripts')
</body>
