<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-marketing">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('marketing.dashboard') }}">
                <i class="bi bi-megaphone-fill"></i> Marketing Portal
            </a>
            <div class="navbar-nav ms-auto">
                <span class="nav-link">
                    <span class="points-badge">
                        <i class="bi bi-star-fill"></i> {{ auth()->guard('marketing')->user()->total_points }} Point
                    </span>
                </span>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> {{ auth()->guard('marketing')->user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <form method="POST" action="{{ route('marketing.logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar">
                @php $currentRoute = Route::currentRouteName(); @endphp
                <a href="{{ route('marketing.dashboard') }}" class="nav-link {{ $currentRoute == 'marketing.dashboard' ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a href="{{ route('marketing.submissions') }}" class="nav-link {{ str_contains($currentRoute, 'marketing.submissions') && !str_contains($currentRoute, 'monitoring') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text"></i> Artikel
                </a>
                <a href="{{ route('marketing.submissions.monitoring') }}" class="nav-link {{ str_contains($currentRoute, 'marketing.submissions.monitoring') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-line"></i> Monitoring Artikel
                </a>
                <hr style="margin: 15px 0; opacity: 0.2;">
                <div style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px; padding: 8px 20px; color: #6c757d; text-transform: uppercase;">
                    <i class="bi bi-lightning-charge text-warning"></i> Fasttrack
                </div>
                <a href="{{ route('marketing.fasttrack.index') }}" class="nav-link {{ str_contains($currentRoute, 'marketing.fasttrack') && !str_contains($currentRoute, 'monitoring') ? 'active' : '' }}">
                    <i class="bi bi-lightning-charge text-warning"></i> Data Fasttrack
                </a>
                <a href="{{ route('marketing.fasttrack.monitoring') }}" class="nav-link {{ str_contains($currentRoute, 'marketing.fasttrack.monitoring') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart text-warning"></i> Monitoring Fasttrack
                </a>
                <hr style="margin: 15px 0; opacity: 0.2;">
                <div style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px; padding: 8px 20px; color: #6c757d; text-transform: uppercase;">
                    <i class="bi bi-journal-bookmark"></i> Pengelolaan Jurnal
                </div>
                <a href="{{ route('marketing.journals.index') }}" class="nav-link {{ str_contains($currentRoute, 'marketing.journals') ? 'active' : '' }}">
                    <i class="bi bi-journal-text"></i> Data Jurnal
                </a>
                <a href="{{ route('marketing.journal-slots.index') }}" class="nav-link {{ str_contains($currentRoute, 'marketing.journal-slots') ? 'active' : '' }}">
                    <i class="bi bi-calendar3"></i> Data Slot
                </a>
                <hr style="margin: 15px 0; opacity: 0.2;">
                <a href="{{ route('marketing.points') }}" class="nav-link {{ $currentRoute == 'marketing.points' ? 'active' : '' }}">
                    <i class="bi bi-trophy"></i> Point Saya
                </a>
                <hr style="margin: 15px 0; opacity: 0.2;">
                <a href="{{ route('marketing.profile.edit') }}" class="nav-link {{ str_contains($currentRoute, 'marketing.profile') ? 'active' : '' }}">
                    <i class="bi bi-person-circle"></i> Profile Saya
                </a>
            </div>
            <div class="col-md-10 content">
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
    @yield('scripts')
</body>
</html>
