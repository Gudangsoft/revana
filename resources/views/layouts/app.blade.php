<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>(function(){var t=localStorage.getItem('adminTheme');if(t==='dark-sidebar')document.documentElement.setAttribute('data-theme','dark-sidebar');})()</script>

    <!-- SEO Meta Tags -->
    <meta name="description" content="{{ $appSettings['full_name'] ?? $appSettings['app_name'] }}">
    <meta name="author" content="{{ $appSettings['app_name'] }}">
    <meta name="robots" content="index, follow">
    <meta name="language" content="Indonesian">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', $appSettings['app_name'] . ' - ' . $appSettings['tagline'])">
    <meta property="og:description" content="{{ $appSettings['tagline'] ?? $appSettings['app_name'] }}">
    <meta property="og:site_name" content="{{ $appSettings['app_name'] }}">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', $appSettings['app_name'] . ' - ' . $appSettings['tagline'])">
    <meta name="twitter:description" content="{{ $appSettings['tagline'] ?? $appSettings['app_name'] }}">
    
    <title>@yield('title', $appSettings['app_name'] . ' - ' . $appSettings['tagline'])</title>
    @if($appSettings['favicon'])
    <link rel="icon" href="{{ asset('storage/' . $appSettings['favicon']) }}" type="image/x-icon">
    @endif
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Tenant Branding Color Override -->
    @php $brandColor = $currentTenant?->branding['primary_color'] ?? null; @endphp
    @if($brandColor)
    <style>:root { --primary-color: {{ $brandColor }}; --secondary-color: {{ $brandColor }}; }</style>
    @endif

    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #7c3aed;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
            --sidebar-width: 280px;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Smooth Scrolling */
        html {
            scroll-behavior: smooth;
        }
        
        * {
            scrollbar-width: thin;
        }

        /* Sidebar Styles */
        .sidebar {
            height: 100vh;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            z-index: 1040;
            transition: transform 0.3s ease-in-out;
            overflow-y: auto;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Custom Scrollbar untuk Sidebar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            transition: background 0.3s;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        /* Firefox Scrollbar */
        .sidebar {
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.3) rgba(255, 255, 255, 0.05);
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
            flex-shrink: 0;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            position: sticky;
            top: 0;
            z-index: 10;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-nav {
            flex: 1;
            padding: 1rem 0 2rem 0;
            position: relative;
        }
        
        /* Scroll Indicator Shadow */
        .sidebar-nav::before {
            content: '';
            position: sticky;
            top: 0;
            left: 0;
            right: 0;
            height: 15px;
            background: linear-gradient(to bottom, rgba(79, 70, 229, 0.3), transparent);
            pointer-events: none;
            z-index: 5;
            display: block;
            margin-bottom: -15px;
        }
        
        .sidebar-nav::after {
            content: '';
            position: sticky;
            bottom: 0;
            left: 0;
            right: 0;
            height: 15px;
            background: linear-gradient(to top, rgba(79, 70, 229, 0.3), transparent);
            pointer-events: none;
            z-index: 5;
            display: block;
            margin-top: -15px;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            white-space: nowrap;
            display: block;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }
        
        .sidebar .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 0;
            background: rgba(255, 255, 255, 0.05);
            transition: width 0.3s ease;
            z-index: -1;
        }

        .sidebar .nav-link:hover::before,
        .sidebar .nav-link.active::before {
            width: 100%;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
            border-left-color: white;
            transform: translateX(2px);
        }

        .sidebar .nav-link i {
            margin-right: 0.5rem;
            width: 20px;
            display: inline-block;
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .sidebar .nav-link:hover i {
            transform: scale(1.1);
        }
        
        .sidebar hr {
            border-color: rgba(255, 255, 255, 0.2);
            margin: 0.5rem 1rem;
        }

        /* Accordion di Sidebar */
        .sidebar .accordion-button {
            background: transparent;
            border: none;
            box-shadow: none;
            padding: 0.75rem 1.5rem;
            border-left: 3px solid transparent;
            transition: all 0.3s;
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
            transform: translateX(2px);
        }

        .sidebar .accordion-button:focus {
            box-shadow: none;
        }

        .sidebar .accordion-body .nav-link {
            font-size: 0.9rem;
            padding-left: 3rem;
        }
        
        .sidebar .accordion-body {
            padding: 0;
            background: rgba(0, 0, 0, 0.1);
        }
        
        .sidebar .accordion-item {
            background: transparent;
        }
        
        .sidebar .accordion-collapse {
            border: none;
        }

        /* Section Label */
        .sidebar-section-label {
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.35);
            padding: 1.1rem 1.5rem 0.3rem;
            margin-top: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .sidebar-section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255,255,255,0.12);
        }

        /* Sidebar Toggle Button (desktop) */
        .sidebar-toggle-btn {
            position: fixed;
            top: 50%;
            left: var(--sidebar-width);
            transform: translate(-50%, -50%);
            z-index: 1045;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            transition: left 0.3s ease-in-out, background 0.2s, box-shadow 0.2s;
            padding: 0;
            line-height: 1;
        }
        .sidebar-toggle-btn:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(79,70,229,0.4);
        }
        .sidebar-toggle-btn:hover i {
            color: white !important;
        }
        .sidebar-toggle-btn i {
            font-size: 0.85rem;
            color: var(--primary-color);
            transition: transform 0.3s ease, color 0.2s;
            pointer-events: none;
        }

        /* Sidebar collapsed state */
        body.sidebar-collapsed .sidebar {
            transform: translateX(-100%);
        }
        body.sidebar-collapsed .sidebar-toggle-btn {
            left: 0px;
        }
        body.sidebar-collapsed .sidebar-toggle-btn i {
            transform: rotate(180deg);
        }
        body.sidebar-collapsed .main-content {
            margin-left: 0;
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
                box-shadow: 2px 0 15px rgba(0, 0, 0, 0.3);
            }

            .sidebar.show-mobile {
                transform: translateX(0) !important;
            }

            /* On mobile, collapsed state doesn't apply */
            body.sidebar-collapsed .sidebar {
                transform: translateX(-100%);
            }
            body.sidebar-collapsed .main-content {
                margin-left: 0;
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
        
        /* Profile Link Hover Effect */
        .profile-link {
            transition: all 0.3s ease;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
        }
        
        .profile-link:hover {
            background-color: rgba(79, 70, 229, 0.1);
            color: var(--primary-color) !important;
            transform: translateY(-2px);
        }
        
        .profile-link i {
            transition: transform 0.3s ease;
        }
        
        .profile-link:hover i {
            transform: scale(1.2);
        }
        
        /* Profile Photo Hover Effect */
        .profile-photo {
            transition: all 0.3s ease;
        }

        .profile-link:hover .profile-photo {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        /* ===== DARK SIDEBAR THEME (Admin) ===== */
        html[data-theme="dark-sidebar"] .sidebar {
            background: #1e293b !important;
        }
        html[data-theme="dark-sidebar"] .sidebar .logo {
            background: #0f172a !important;
            border-bottom-color: rgba(255,255,255,0.06);
        }
        html[data-theme="dark-sidebar"] .sidebar-nav::before {
            background: linear-gradient(to bottom, rgba(15,23,42,0.4), transparent);
        }
        html[data-theme="dark-sidebar"] .sidebar-nav::after {
            background: linear-gradient(to top, rgba(15,23,42,0.4), transparent);
        }
        html[data-theme="dark-sidebar"] .sidebar .accordion-body {
            background: rgba(0,0,0,0.15);
        }

        /* Tombol toggle tema — di navbar putih admin */
        #adminThemeBtn {
            color: #64748b;
            background: transparent;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 0.8rem;
            line-height: 1;
            transition: all 0.2s;
            cursor: pointer;
        }
        #adminThemeBtn:hover {
            color: #1e293b;
            border-color: #94a3b8;
            background: #f1f5f9;
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay" onclick="toggleMobileMenu()"></div>

    <!-- Desktop Sidebar Toggle Button -->
    <button class="sidebar-toggle-btn d-none d-lg-flex" id="sidebarToggleBtn" onclick="toggleSidebar()" title="Sembunyikan / Tampilkan sidebar">
        <i class="bi bi-chevron-left" id="sidebarToggleIcon"></i>
    </button>

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
        <nav class="nav flex-column sidebar-nav">
            @if(auth()->check() && auth()->user()->isPicReviewer())
                @include('admin.partials.sidebar-pic-reviewer')
            @else
                @yield('sidebar')
            @endif
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light rounded">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1">@yield('page-title', 'Dashboard')</span>
                <div class="ms-auto d-flex align-items-center flex-wrap gap-2">
                    @if(auth()->user()->role === 'admin')
                    <form action="{{ route('admin.search') }}" method="GET"
                          class="d-flex align-items-center me-1" role="search" id="globalSearchForm">
                        <div class="input-group input-group-sm" style="width:240px;">
                            <input type="text" name="q" id="globalSearchInput"
                                   class="form-control border-0 shadow-sm"
                                   placeholder="Cari penulis, ID, judul…"
                                   value="{{ request('q') }}"
                                   autocomplete="off"
                                   style="border-radius:20px 0 0 20px; background:#f1f5f9;">
                            <button class="btn btn-primary btn-sm" type="submit"
                                    style="border-radius:0 20px 20px 0;">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>
                    @endif
                    <a href="@if(auth()->user()->role === 'admin'){{ route('admin.profile.edit') }}@else{{ route('reviewer.profile.edit') }}@endif" 
                       class="text-decoration-none text-dark me-2 d-none d-md-flex align-items-center profile-link">
                        @if(auth()->user()->photo)
                            <img src="{{ asset('storage/' . auth()->user()->photo) }}" 
                                 alt="Profile Photo" 
                                 class="rounded-circle me-2 profile-photo" 
                                 width="35" 
                                 height="35" 
                                 style="object-fit: cover; border: 2px solid #4f46e5;">
                        @else
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center me-2" 
                                 style="width: 35px; height: 35px; border: 2px solid #4f46e5;">
                                <i class="bi bi-person-fill text-white" style="font-size: 1.2rem;"></i>
                            </div>
                        @endif
                        <span>
                            @if(auth()->user()->role === 'admin')
                                @if(!empty($currentTenant))
                                    {{ Str::limit(auth()->user()->name, 20) }}
                                @else
                                    Admin {{ $appSettings['app_name'] }}
                                @endif
                            @else
                                {{ auth()->user()->name }}
                            @endif
                        </span>
                    </a>
                    <a href="@if(auth()->user()->role === 'admin'){{ route('admin.profile.edit') }}@else{{ route('reviewer.profile.edit') }}@endif"
                       class="text-decoration-none text-dark me-2 d-md-none d-flex align-items-center profile-link">
                        @if(auth()->user()->photo)
                            <img src="{{ asset('storage/' . auth()->user()->photo) }}"
                                 alt="Profile Photo"
                                 class="rounded-circle me-2 profile-photo"
                                 width="32"
                                 height="32"
                                 style="object-fit: cover; border: 2px solid #4f46e5;">
                        @else
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center me-2"
                                 style="width: 32px; height: 32px; border: 2px solid #4f46e5;">
                                <i class="bi bi-person-fill text-white" style="font-size: 1rem;"></i>
                            </div>
                        @endif
                        <span>
                            @if(auth()->user()->role === 'admin')
                                @if(!empty($currentTenant))
                                    {{ Str::limit(auth()->user()->name, 15) }}
                                @else
                                    Admin
                                @endif
                            @else
                                {{ Str::limit(auth()->user()->name, 15) }}
                            @endif
                        </span>
                    </a>
                    <button id="adminThemeBtn" onclick="toggleTheme()" title="">
                        <i class="bi bi-moon-stars-fill" id="adminThemeIcon"></i>
                        <span id="adminThemeLabel" class="d-none d-lg-inline ms-1"></span>
                    </button>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-box-arrow-right"></i> <span class="d-none d-sm-inline">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </nav>

        {{-- Impersonate Banner (Tenant) --}}
        @if(session('impersonating'))
        <div class="alert alert-warning d-flex align-items-center justify-content-between mb-2 py-2" role="alert"
             style="border-left: 4px solid #f59e0b; border-radius: 6px;">
            <div>
                <i class="bi bi-person-badge-fill me-2 text-warning"></i>
                <strong>Mode Impersonate Aktif</strong>
                — Anda login sebagai admin tenant atas nama
                <strong>{{ session('impersonate_by', 'Super Admin') }}</strong>
            </div>
            <form action="{{ route('impersonate.stop') }}" method="POST" class="mb-0">
                @csrf
                <button type="submit" class="btn btn-warning btn-sm">
                    <i class="bi bi-box-arrow-left me-1"></i>Kembali ke Super Admin
                </button>
            </form>
        </div>
        @endif

        {{-- Login As User Banner (Admin impersonating reviewer/user) --}}
        @if(session('admin_user_impersonating'))
        <div class="alert alert-info d-flex align-items-center justify-content-between mb-2 py-2" role="alert"
             style="border-left: 4px solid #3b82f6; border-radius: 6px; background:#eff6ff;">
            <div>
                <i class="bi bi-person-fill-check me-2 text-info"></i>
                <strong>Mode Login As Aktif</strong>
                — Anda sedang melihat sistem sebagai
                <strong>{{ Auth::user()?->name }}</strong>
                <span class="badge bg-secondary ms-1">{{ Auth::user()?->role }}</span>
            </div>
            <form action="{{ route('admin.users.return-to-admin') }}" method="POST" class="mb-0">
                @csrf
                <button type="submit" class="btn btn-info btn-sm text-white">
                    <i class="bi bi-box-arrow-left me-1"></i>Kembali ke Admin
                </button>
            </form>
        </div>
        @endif

        <!-- Alerts -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        
        {{-- Special Point Award Notification --}}
        @if(session('point_awarded'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <div class="me-3 text-center">
                    <i class="bi bi-trophy-fill" style="font-size: 2.5rem; color: #ffc107;"></i>
                </div>
                <div>
                    <h5 class="alert-heading mb-1">
                        <i class="bi bi-star-fill text-warning"></i> Point Diberikan!
                    </h5>
                    <p class="mb-1">
                        <strong>{{ session('pic_name') }}</strong> mendapatkan 
                        <span class="badge bg-success fs-6">+{{ session('points_earned') }} point</span>
                        untuk tugas <strong>{{ session('step_label') }}</strong>
                    </p>
                    <small class="text-muted">
                        <i class="bi bi-graph-up-arrow"></i> Total point {{ session('pic_name') }}: <strong>{{ session('total_points') }} point</strong>
                    </small>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        {{-- Sync Warning (muncul saat login admin) --}}
        @if(session('sync_warning'))
        <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="bi bi-arrow-repeat fs-4 me-3"></i>
            <div>{!! session('sync_warning') !!}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
        @endif

        {{-- Sync OK (muncul saat login & semua data sinkron) --}}
        @if(session('sync_info'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle fs-4 me-3"></i>
            <div>{!! session('sync_info') !!}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
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
    
    <!-- Sidebar & Mobile Menu Script -->
    <script>
        // ── Desktop sidebar toggle ──────────────────────────────────────
        function toggleSidebar() {
            const collapsed = document.body.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', collapsed ? '1' : '0');
        }

        // Restore saved state on load (before paint to avoid flash)
        (function () {
            if (localStorage.getItem('sidebarCollapsed') === '1') {
                document.body.classList.add('sidebar-collapsed');
            }
        })();

        // ── Mobile sidebar toggle ───────────────────────────────────────
        function toggleMobileMenu() {
            const sidebar  = document.getElementById('sidebar');
            const overlay  = document.getElementById('mobileOverlay');
            sidebar.classList.toggle('show-mobile');
            overlay.classList.toggle('show');
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Close mobile menu when clicking a nav link
            document.querySelectorAll('.sidebar .nav-link').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (window.innerWidth <= 991) toggleMobileMenu();
                });
            });

            // Close mobile menu on resize to desktop
            window.addEventListener('resize', function () {
                if (window.innerWidth > 991) {
                    document.getElementById('sidebar').classList.remove('show-mobile');
                    document.getElementById('mobileOverlay').classList.remove('show');
                }
            });
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
        var icon  = document.getElementById('adminThemeIcon');
        var label = document.getElementById('adminThemeLabel');
        var btn   = document.getElementById('adminThemeBtn');
        if (icon)  icon.className = isDark ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
        if (label) label.textContent = isDark ? 'Tema Terang' : 'Tema Gelap';
        if (btn)   btn.title = isDark ? 'Kembali ke tema terang' : 'Coba tema gelap sidebar';
    }
    function toggleTheme() {
        var isDark = document.documentElement.getAttribute('data-theme') === 'dark-sidebar';
        var next = isDark ? 'default' : 'dark-sidebar';
        localStorage.setItem('adminTheme', next);
        applyTheme(next);
    }
    document.addEventListener('DOMContentLoaded', function() {
        var saved = localStorage.getItem('adminTheme') || 'default';
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
    @stack('scripts')
</body>
</html>
