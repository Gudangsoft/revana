<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', $appSettings['app_name'] . ' - ' . $appSettings['tagline'])</title>
    @if($appSettings['favicon'])
    <link rel="icon" href="{{ asset('storage/' . $appSettings['favicon']) }}" type="image/x-icon">
    @endif
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #7c3aed;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
            --sidebar-width: 250px;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Sidebar Styles */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            z-index: 1040;
            transition: transform 0.3s ease-in-out;
            overflow-y: auto;
        }

        .sidebar.hide-mobile {
            transform: translateX(-100%);
        }

        .sidebar .logo {
            padding: 1.5rem;
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1.5rem;
            transition: all 0.3s;
            border-left: 3px solid transparent;
            white-space: nowrap;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
            border-left-color: white;
        }

        .sidebar .nav-link i {
            margin-right: 0.5rem;
            width: 20px;
        }

        /* Accordion di Sidebar */
        .sidebar .accordion-button {
            background: transparent;
            border: none;
            box-shadow: none;
            padding: 0.75rem 1.5rem;
            border-left: 3px solid transparent;
        }

        .sidebar .accordion-button:not(.collapsed) {
            background: rgba(255,255,255,0.1);
            border-left-color: white;
            color: white;
        }

        .sidebar .accordion-button::after {
            filter: brightness(0) invert(1);
            margin-left: auto;
        }

        .sidebar .accordion-button:hover {
            background: rgba(255,255,255,0.1);
        }

        .sidebar .accordion-button:focus {
            box-shadow: none;
        }

        .sidebar .accordion-body .nav-link {
            font-size: 0.9rem;
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1050;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            width: 45px;
            height: 45px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            visibility: hidden;
            opacity: 0;
        }

        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1030;
        }

        .mobile-overlay.show {
            display: block;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            transition: margin-left 0.3s ease-in-out;
        }

        .navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
            border-radius: 8px;
        }

        .navbar-brand {
            font-size: 1.1rem;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
            padding: 1rem 1.5rem;
        }

        .stats-card {
            border-left: 4px solid;
        }

        .stats-card.primary {
            border-left-color: var(--primary-color);
        }

        .stats-card.success {
            border-left-color: var(--success-color);
        }

        .stats-card.warning {
            border-left-color: var(--warning-color);
        }

        .stats-card.danger {
            border-left-color: var(--danger-color);
        }

        .badge-custom {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
        }

        .btn {
            border-radius: 6px;
            padding: 0.5rem 1.25rem;
            font-weight: 500;
        }

        .btn-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.875rem;
        }

        .table {
            background: white;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* Responsive table wrapper */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 1rem;
        }

        /* Better responsive tables */
        @media (max-width: 768px) {
            .table-responsive table {
                min-width: 600px;
            }
        }

        /* Form responsive */
        .form-control,
        .form-select {
            font-size: 1rem;
        }

        /* Alert responsive */
        .alert {
            font-size: 0.95rem;
        }

        /* Mobile Responsive */
        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show-mobile {
                transform: translateX(0);
            }

            .mobile-menu-toggle {
                display: none;
                align-items: center;
                justify-content: center;
                visibility: hidden;
                opacity: 0;
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
                padding-top: 4rem;
            }

            .navbar {
                margin-bottom: 1rem;
            }

            .navbar-brand {
                font-size: 0.95rem;
            }

            .card-header {
                padding: 0.75rem 1rem;
                font-size: 0.95rem;
            }

            .card-body {
                padding: 1rem;
            }

            /* Mobile friendly buttons */
            .btn-group-sm > .btn,
            .btn-sm {
                padding: 0.35rem 0.5rem;
                font-size: 0.8rem;
            }

            /* Stack action buttons on mobile */
            .d-flex.gap-2 {
                flex-wrap: wrap;
                gap: 0.5rem !important;
            }

            /* Compact button groups */
            .btn-group {
                flex-wrap: wrap;
            }

            /* Make stats cards stack better */
            .col-md-3,
            .col-md-4,
            .col-md-6,
            .col-md-8 {
                margin-bottom: 1rem;
            }

            /* Better spacing for mobile */
            .row {
                margin-left: -0.5rem;
                margin-right: -0.5rem;
            }

            .row > * {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }

            /* Reduce table font size on mobile */
            .table {
                font-size: 0.85rem;
            }

            .table th,
            .table td {
                padding: 0.5rem 0.35rem;
                white-space: nowrap;
            }

            /* Badge size adjustment */
            .badge {
                font-size: 0.75rem;
                padding: 0.25rem 0.5rem;
            }

            /* Card adjustments */
            .card-title {
                font-size: 1rem;
            }

            /* Modal adjustments */
            .modal-dialog {
                margin: 0.5rem;
            }

            /* Hide less important columns on mobile */
            .table .hide-mobile {
                display: none;
            }

            /* Compact nav tabs */
            .nav-tabs .nav-link {
                padding: 0.5rem 0.75rem;
                font-size: 0.85rem;
            }

            .nav-tabs .badge {
                font-size: 0.7rem;
                padding: 0.15rem 0.4rem;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 0.75rem;
                padding-top: 3.5rem;
            }

            .navbar {
                padding: 0.5rem;
            }

            .navbar-brand {
                font-size: 0.85rem;
            }

            .card {
                margin-bottom: 1rem;
            }

            .card-body {
                padding: 0.75rem;
            }

            h1, .h1 {
                font-size: 1.5rem;
            }

            h2, .h2 {
                font-size: 1.3rem;
            }

            h3, .h3 {
                font-size: 1.1rem;
            }

            h4, .h4 {
                font-size: 1rem;
            }

            /* Even more compact tables */
            .table {
                font-size: 0.75rem;
            }

            .table th,
            .table td {
                padding: 0.4rem 0.25rem;
            }

            /* Compact buttons */
            .btn {
                padding: 0.4rem 0.75rem;
                font-size: 0.85rem;
            }

            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }

            /* Single column for forms on mobile */
            .row.mb-3 .col-md-4,
            .row.mb-3 .col-md-6,
            .row.mb-3 .col-md-8 {
                margin-bottom: 0.5rem;
            }

            /* Compact alerts */
            .alert {
                padding: 0.75rem;
                font-size: 0.85rem;
            }

            /* Compact pagination */
            .pagination {
                font-size: 0.85rem;
            }

            .page-link {
                padding: 0.375rem 0.75rem;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay" onclick="toggleMobileMenu()"></div>

    <!-- Mobile Menu Toggle Button -->
    <button class="mobile-menu-toggle" id="menuToggle" onclick="toggleMobileMenu()">
        <i class="bi bi-list fs-4"></i>
    </button>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="logo">
            @if($appSettings['logo'])
                <img src="{{ asset('storage/' . $appSettings['logo']) }}" alt="Logo" style="max-height: 40px; max-width: 180px;">
            @else
                <i class="bi bi-journal-check"></i> {{ $appSettings['app_name'] }}
            @endif
        </div>
        <nav class="nav flex-column mt-4">
            @yield('sidebar')
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light rounded">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1">@yield('page-title', 'Dashboard')</span>
                <div class="ms-auto d-flex align-items-center flex-wrap gap-2">
                    <span class="me-2 d-none d-md-inline">
                        <i class="bi bi-person-circle"></i> 
                        @if(auth()->user()->role === 'admin')
                            Admin {{ $appSettings['app_name'] }}
                        @else
                            {{ auth()->user()->name }}
                        @endif
                    </span>
                    <span class="me-2 d-md-none">
                        <i class="bi bi-person-circle"></i> 
                        @if(auth()->user()->role === 'admin')
                            Admin
                        @else
                            {{ Str::limit(auth()->user()->name, 15) }}
                        @endif
                    </span>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-box-arrow-right"></i> <span class="d-none d-sm-inline">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </nav>

        <!-- Alerts -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Page Content -->
        @yield('content')
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Mobile Menu Script -->
    <script>
        function toggleMobileMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileOverlay');
            
            sidebar.classList.toggle('show-mobile');
            overlay.classList.toggle('show');
        }

        // Close menu when clicking on a link
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('.sidebar .nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 991) {
                        toggleMobileMenu();
                    }
                });
            });

            // Close menu when resizing to desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth > 991) {
                    document.getElementById('sidebar').classList.remove('show-mobile');
                    document.getElementById('mobileOverlay').classList.remove('show');
                }
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>
