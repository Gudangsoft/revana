<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Anti-flash: apply saved theme sebelum CSS render --}}
    <script>(function(){var t=localStorage.getItem('picTheme');if(t==='dark-sidebar')document.documentElement.setAttribute('data-theme','dark-sidebar');if(localStorage.getItem('picSidebarCollapsed')==='1')document.documentElement.setAttribute('data-sidebar','collapsed');})()</script>

    <title>@yield('title', 'PIC Dashboard') - {{ $appSettings['app_name'] }}</title>
    @if($appSettings['favicon'])
    <link rel="icon" href="{{ asset('storage/' . $appSettings['favicon']) }}" type="image/x-icon">
    @endif
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        .main-container {
            display: flex;
            min-height: calc(100vh - 56px);
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            min-width: 250px;
            max-width: 250px;
            flex-shrink: 0;
            background: white;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            padding: 20px 0;
            overflow-y: auto;
            overflow-x: hidden;
            position: relative;
            transition: width 0.25s ease, min-width 0.25s ease, padding 0.25s ease, box-shadow 0.25s ease;
        }
        html[data-sidebar="collapsed"] .sidebar,
        .sidebar.sidebar-collapsed {
            width: 0 !important;
            min-width: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
            box-shadow: none !important;
        }
        /* Sidebar toggle button */
        #sidebarToggleBtn {
            color: rgba(255,255,255,0.8);
            background: transparent;
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 6px;
            padding: 4px 8px;
            line-height: 1;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        #sidebarToggleBtn:hover {
            color: #fff;
            border-color: rgba(255,255,255,0.6);
            background: rgba(255,255,255,0.1);
        }
        
        .sidebar .nav-link {
            color: #333;
            padding: 12px 20px;
            border-left: 3px solid transparent;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
        }
        .sidebar .nav-link:hover {
            background-color: #f8f9fa;
            border-left-color: #667eea;
        }
        .sidebar .nav-link.active {
            background-color: #667eea;
            color: white;
            border-left-color: #764ba2;
        }
        
        /* Content area */
        .content {
            flex: 1;
            padding: 30px;
        }
        
        .page-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .card {
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
            top: 50%;
            transform: translateY(-50%);
            background: #333;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            white-space: nowrap;
            z-index: 1000;
            margin-left: 10px;
        }
        
        /* ===== DARK SIDEBAR THEME ===== */
        html[data-theme="dark-sidebar"] .navbar {
            background: #0f172a !important;
            border-bottom: 1px solid #1e293b;
        }
        html[data-theme="dark-sidebar"] .sidebar {
            background: #1e293b;
            box-shadow: 1px 0 0 rgba(255,255,255,0.06);
        }
        html[data-theme="dark-sidebar"] .page-header {
            border-left: 3px solid #6366f1;
        }

        /* Tombol toggle tema */
        #themeToggleBtn {
            color: rgba(255,255,255,0.72);
            background: transparent;
            border: 1px solid rgba(255,255,255,0.22);
            border-radius: 6px;
            padding: 4px 8px;
            line-height: 1;
            transition: all 0.2s;
            font-size: 0.8rem;
        }
        #themeToggleBtn:hover {
            color: #fff;
            border-color: rgba(255,255,255,0.55);
            background: rgba(255,255,255,0.08);
        }

        @yield('styles')
    </style>
</head>
<body>
    @if(session('admin_impersonating'))
    <div class="bg-warning text-dark py-1 px-3 d-flex align-items-center gap-2" style="font-size:.85rem; border-bottom: 2px solid #ffc107;">
        <i class="bi bi-eye-fill"></i>
        <strong>Mode Admin:</strong> Anda sedang melihat sebagai <strong>{{ auth()->guard('pic')->user()->name }}</strong>
        <form method="POST" action="{{ route('admin.pics.return-to-admin') }}" class="ms-auto mb-0">
            @csrf
            <button type="submit" class="btn btn-dark btn-sm fw-bold">
                <i class="bi bi-box-arrow-left me-1"></i>Kembali ke Admin
            </button>
        </form>
    </div>
    @endif

    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('pic.author.dashboard') }}">
                @if($appSettings['logo'])
                    <img src="{{ asset('storage/' . $appSettings['logo']) }}" alt="Logo" style="max-height: 30px;">
                @else
                    <i class="bi bi-person-badge"></i> {{ $appSettings['app_name'] }} PIC
                @endif
            </a>

            {{-- Indikator impersonasi di kiri navbar (selalu terlihat, tidak perlu expand) --}}
            @if(session('admin_impersonating'))
            <span class="badge bg-warning text-dark ms-2 d-flex align-items-center gap-1 flex-shrink-0" style="font-size:.72rem;">
                <i class="bi bi-eye-fill"></i>
                <span class="d-none d-sm-inline">Mode Admin</span>
            </span>
            @endif

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center gap-2">
                    {{-- Tombol toggle sidebar --}}
                    <li class="nav-item">
                        <button id="sidebarToggleBtn" onclick="toggleSidebar()" title="Sembunyikan/Tampilkan Menu">
                            <i class="bi bi-layout-sidebar" id="sidebarToggleIcon"></i>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button id="themeToggleBtn" onclick="toggleTheme()" title="">
                            <i class="bi bi-moon-stars-fill" id="themeIcon"></i>
                            <span id="themeLabel" class="d-none d-lg-inline ms-1"></span>
                        </button>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> {{ auth()->guard('pic')->user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text small">Role: {{ auth()->guard('pic')->user()->role }}</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a href="{{ route('pic.points.index') }}" class="dropdown-item">
                                    <i class="bi bi-trophy text-warning"></i> Point Saya
                                </a>
                            </li>
                            @if(session('admin_impersonating'))
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('admin.pics.return-to-admin') }}" class="px-1 py-1">
                                    @csrf
                                    <button type="submit" class="btn btn-warning btn-sm w-100 fw-bold">
                                        <i class="bi bi-box-arrow-left me-1"></i>Kembali ke Admin
                                    </button>
                                </form>
                            </li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#picLogoutModal">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </button>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <div class="sidebar @yield('sidebar-class')">
            @yield('sidebar')
        </div>
        <div class="content">
            @if(View::hasSection('page-title') && trim(View::yieldContent('page-title')) !== '')
            <div class="page-header">
                <h2>@yield('page-title')</h2>
            </div>
            @endif
            
            {{-- Flash Messages --}}
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {!! session('success') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            {{-- Global Validation Error Summary (any page) --}}
            @if($errors->any() && !request()->routeIs('pic.submissions.create') && !request()->routeIs('pic.fasttrack.create'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Terdapat kesalahan pada input:</strong>
                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            
            @if(session('point_earned'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-trophy-fill" style="font-size: 2.5rem; color: #ffc107;"></i>
                    </div>
                    <div>
                        <h5 class="mb-1"><i class="bi bi-star-fill text-warning"></i> Selamat! Anda Mendapatkan Point!</h5>
                        <p class="mb-1">
                            <strong>+{{ session('point_earned') }} point</strong> untuk tugas <strong>{{ session('point_step') }}</strong>
                        </p>
                        <small class="text-muted">
                            Total point Anda sekarang: <strong>{{ session('total_points') }} point</strong>
                        </small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            
            @yield('content')
        </div>
    </div>

    {{-- Toast Notifications --}}
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999">
        @if(session('success'))
        <div id="toastSuccess" class="toast align-items-center text-bg-success border-0 shadow" role="alert" data-bs-delay="6000">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-check-circle-fill me-2"></i> {!! session('success') !!}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
        @endif
        @if(session('error'))
        <div id="toastError" class="toast align-items-center text-bg-danger border-0 shadow" role="alert" data-bs-delay="8000">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
        @endif
        @if($errors->any())
        <div id="toastValidation" class="toast align-items-center text-bg-warning border-0 shadow" role="alert" data-bs-delay="8000">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-exclamation-circle-fill me-2"></i> <strong>{{ $errors->count() }} kesalahan</strong> ditemukan. Periksa form di bawah.
                </div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show toasts on page load
        document.addEventListener('DOMContentLoaded', function() {
            ['toastSuccess', 'toastError', 'toastValidation'].forEach(function(id) {
                var el = document.getElementById(id);
                if (el) { new bootstrap.Toast(el).show(); }
            });
        });
        // Sidebar toggle
        function toggleSidebar() {
            var sidebar = document.querySelector('.sidebar');
            var isCollapsed = sidebar.classList.toggle('sidebar-collapsed');
            localStorage.setItem('picSidebarCollapsed', isCollapsed ? '1' : '0');
            document.documentElement.setAttribute('data-sidebar', isCollapsed ? 'collapsed' : 'visible');
            var icon = document.getElementById('sidebarToggleIcon');
            if (icon) icon.className = isCollapsed ? 'bi bi-layout-sidebar-inset' : 'bi bi-layout-sidebar';
        }
        document.addEventListener('DOMContentLoaded', function() {
            var collapsed = localStorage.getItem('picSidebarCollapsed') === '1';
            var sidebar = document.querySelector('.sidebar');
            var icon = document.getElementById('sidebarToggleIcon');
            if (collapsed && sidebar) sidebar.classList.add('sidebar-collapsed');
            if (icon) icon.className = collapsed ? 'bi bi-layout-sidebar-inset' : 'bi bi-layout-sidebar';
        });
    </script>

    <!-- PIC Logout Modal -->
    <div class="modal fade" id="picLogoutModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning text-dark border-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-arrow-repeat me-2"></i> Sebelum Logout
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4 text-center">
                    <i class="bi bi-trophy-fill text-warning" style="font-size:3rem"></i>
                    <p class="fw-semibold mt-3 mb-1">Sinkronkan point sebelum logout?</p>
                    <p class="text-muted small mb-0">Pastikan semua riwayat tugas dan total point Anda tersimpan dengan benar sebelum keluar.</p>
                </div>
                <div class="modal-footer border-0 justify-content-center gap-2 pb-4">
                    <form method="POST" action="{{ route('pic.points.sync') }}?redirect=logout" id="picSyncLogoutForm">
                        @csrf
                        <button type="submit" class="btn btn-warning fw-semibold px-4" id="btnPicSyncLogout">
                            <i class="bi bi-arrow-repeat me-1"></i> Sync & Logout
                        </button>
                    </form>
                    <form method="POST" action="{{ route('pic.logout') }}" class="d-inline">
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
    <script>
        var pslf = document.getElementById('picSyncLogoutForm');
        if (pslf) pslf.addEventListener('submit', function() {
            var btn = document.getElementById('btnPicSyncLogout');
            if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyinkronkan...'; }
        });
    </script>
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
    function applyTheme(theme) {
        var isDark = theme === 'dark-sidebar';
        document.documentElement.setAttribute('data-theme', isDark ? 'dark-sidebar' : 'default');
        var icon  = document.getElementById('themeIcon');
        var label = document.getElementById('themeLabel');
        var btn   = document.getElementById('themeToggleBtn');
        if (icon)  icon.className = isDark ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
        if (label) label.textContent = isDark ? 'Tema Terang' : 'Tema Gelap';
        if (btn)   btn.title = isDark ? 'Kembali ke tema terang' : 'Coba tema gelap sidebar';
    }
    function toggleTheme() {
        var isDark = document.documentElement.getAttribute('data-theme') === 'dark-sidebar';
        var next = isDark ? 'default' : 'dark-sidebar';
        localStorage.setItem('picTheme', next);
        applyTheme(next);
    }
    // Init icon sesuai tema tersimpan + tampilkan pengumuman sekali
    document.addEventListener('DOMContentLoaded', function() {
        var saved = localStorage.getItem('picTheme') || 'default';
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
</html>
