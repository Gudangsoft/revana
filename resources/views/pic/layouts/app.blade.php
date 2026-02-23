<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
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
        
        @yield('styles')
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('pic.author.dashboard') }}">
                @if($appSettings['logo'])
                    <img src="{{ asset('storage/' . $appSettings['logo']) }}" alt="Logo" style="max-height: 30px;">
                @else
                    <i class="bi bi-person-badge"></i> {{ $appSettings['app_name'] }} PIC
                @endif
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center gap-2">
                    {{-- Tombol sync point strategis di navbar --}}
                    <li class="nav-item">
                        <form method="POST" action="{{ route('pic.points.sync') }}" class="d-inline" id="navSyncForm">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-warning fw-semibold" id="navSyncBtn" title="Sinkronkan Point Saya">
                                <i class="bi bi-arrow-repeat" id="navSyncIcon"></i>
                                <span class="d-none d-md-inline"> Sync Point</span>
                            </button>
                        </form>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // No sidebar collapse functionality
        // Sync button loading state
        var nsf = document.getElementById('navSyncForm');
        if (nsf) nsf.addEventListener('submit', function() {
            var btn = document.getElementById('navSyncBtn');
            var ico = document.getElementById('navSyncIcon');
            if (btn) { btn.disabled = true; ico.style.animation = 'spin .8s linear infinite'; }
        });
    </script>
    <style>@keyframes spin{from{transform:rotate(0)}to{transform:rotate(360deg)}}</style>

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
    @yield('scripts')
</body>
</html>
