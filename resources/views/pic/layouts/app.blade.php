<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="noindex, nofollow">
    
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
            transition: all 0.3s ease;
            position: relative;
        }
        
        /* Collapsed Sidebar */
        .sidebar.collapsed {
            width: 60px;
            min-width: 60px;
            max-width: 60px;
        }
        
        .sidebar.collapsed .nav-link {
            padding: 12px 18px;
            justify-content: center;
        }
        
        .sidebar.collapsed .nav-link span,
        .sidebar.collapsed .nav-link .badge,
        .sidebar.collapsed .sidebar-section-header span,
        .sidebar.collapsed .sidebar-section-header {
            display: none !important;
        }
        
        .sidebar.collapsed .nav-link i {
            margin-right: 0 !important;
            font-size: 1.2rem;
        }
        
        /* Toggle Button */
        .sidebar-toggle {
            position: absolute;
            top: 10px;
            right: -12px;
            width: 24px;
            height: 24px;
            background: #667eea;
            border: 2px solid white;
            border-radius: 50%;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 100;
            font-size: 0.7rem;
            transition: all 0.3s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .sidebar-toggle:hover {
            background: #764ba2;
            transform: scale(1.1);
        }
        
        .sidebar.collapsed .sidebar-toggle {
            right: -12px;
        }
        
        .sidebar.collapsed .sidebar-toggle i {
            transform: rotate(180deg);
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
        
        /* Content area adjusts when sidebar collapsed */
        .content {
            flex: 1;
            padding: 30px;
            transition: all 0.3s ease;
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
        
        /* Tooltip for collapsed sidebar */
        .sidebar.collapsed .nav-link {
            position: relative;
        }
        
        .sidebar.collapsed .nav-link:hover::after {
            content: attr(data-title);
            position: absolute;
            left: 100%;
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
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> {{ auth()->guard('pic')->user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text small">Role: {{ auth()->guard('pic')->user()->role }}</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('pic.logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <div class="sidebar @yield('sidebar-class')" id="sidebar">
            <div class="sidebar-toggle" onclick="toggleSidebar()" title="Toggle Menu">
                <i class="bi bi-chevron-left"></i>
            </div>
            @yield('sidebar')
        </div>
        <div class="content">
            @hasSection('page-title')
                @if(trim(View::yieldContent('page-title')) !== '')
                <div class="page-header">
                    <h2>@yield('page-title')</h2>
                </div>
                @endif
            @endhasSection
            
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
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
            
            // Save state to localStorage
            if (sidebar.classList.contains('collapsed')) {
                localStorage.setItem('sidebarCollapsed', 'true');
            } else {
                localStorage.setItem('sidebarCollapsed', 'false');
            }
        }
        
        // Check if page should auto-collapse sidebar
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const autoCollapse = sidebar.classList.contains('auto-collapse');
            
            if (autoCollapse) {
                sidebar.classList.add('collapsed');
            } else {
                // Restore from localStorage for other pages
                const savedState = localStorage.getItem('sidebarCollapsed');
                if (savedState === 'true') {
                    sidebar.classList.add('collapsed');
                }
            }
        });
    </script>
    @yield('scripts')
</body>
</html>
