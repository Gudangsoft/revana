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
                <span class="nav-link d-flex align-items-center gap-2">
                    <span class="points-badge">
                        <i class="bi bi-star-fill"></i> {{ auth()->guard('marketing')->user()->submissions()->count() }} Point
                    </span>
                    <a href="{{ route('marketing.refresh-points') }}" class="btn btn-sm btn-light rounded-circle" title="Refresh Point" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-arrow-clockwise text-success"></i>
                    </a>
                </span>
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
                @php $currentRoute = Route::currentRouteName(); @endphp
                <a href="{{ route('marketing.dashboard') }}" class="nav-link {{ $currentRoute == 'marketing.dashboard' ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                @rolecap('marketing', 'buat_submission')
                <a href="{{ route('marketing.submissions') }}" class="nav-link {{ str_contains($currentRoute, 'marketing.submissions') && !str_contains($currentRoute, 'monitoring') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text"></i> Artikel
                </a>
                <a href="{{ route('marketing.submissions.monitoring') }}" class="nav-link {{ str_contains($currentRoute, 'marketing.submissions.monitoring') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-line"></i> Monitoring Artikel
                </a>
                @endrolecap
                <hr style="margin: 15px 0; opacity: 0.2;">
                @feature('fasttrack')
                @rolecap('marketing', 'fasttrack')
                <a href="{{ route('marketing.fasttrack.index') }}" class="nav-link {{ str_contains($currentRoute, 'marketing.fasttrack') && !str_contains($currentRoute, 'monitoring') ? 'active' : '' }}">
                    <i class="bi bi-lightning-charge text-warning"></i> Fasttrack
                </a>
                <a href="{{ route('marketing.fasttrack.monitoring') }}" class="nav-link {{ str_contains($currentRoute, 'marketing.fasttrack.monitoring') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart text-warning"></i> Monitoring Fasttrack
                </a>
                @endrolecap
                @endfeature
                <hr style="margin: 15px 0; opacity: 0.2;">
                <div style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px; padding: 8px 20px; color: #0dcaf0; text-transform: uppercase;">
                    <i class="bi bi-briefcase-fill"></i> Pengelolaan BKD
                </div>
                <a href="{{ route('marketing.submissions.create', ['program' => 'bkd']) }}" class="nav-link {{ $currentRoute == 'marketing.submissions.create' && request('program') === 'bkd' ? 'active' : '' }}">
                    <i class="bi bi-plus-circle-fill text-info"></i> Input Data BKD
                </a>
                <a href="{{ route('marketing.submissions', ['program' => 'bkd']) }}" class="nav-link {{ str_contains($currentRoute, 'marketing.submissions') && !str_contains($currentRoute, 'monitoring') && !str_contains($currentRoute, 'create') && request('program') === 'bkd' ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text text-info"></i> Data Submit BKD
                </a>
                <a href="{{ route('marketing.submissions.monitoring', ['program' => 'bkd']) }}" class="nav-link {{ str_contains($currentRoute, 'marketing.submissions.monitoring') && request('program') === 'bkd' ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-line text-info"></i> Monitoring Proses BKD
                </a>
                <hr style="margin: 15px 0; opacity: 0.2;">
                <div style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px; padding: 8px 20px; color: #198754; text-transform: uppercase;">
                    <i class="bi bi-folder2-open"></i> Pengelolaan JAFA
                </div>
                <a href="{{ route('marketing.submissions.create', ['program' => 'jafa']) }}" class="nav-link {{ $currentRoute == 'marketing.submissions.create' && request('program') === 'jafa' ? 'active' : '' }}">
                    <i class="bi bi-plus-circle-fill text-success"></i> Input Data JAFA
                </a>
                <a href="{{ route('marketing.submissions', ['program' => 'jafa']) }}" class="nav-link {{ str_contains($currentRoute, 'marketing.submissions') && !str_contains($currentRoute, 'monitoring') && !str_contains($currentRoute, 'create') && request('program') === 'jafa' ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text text-success"></i> Data Submit JAFA
                </a>
                <a href="{{ route('marketing.submissions.monitoring', ['program' => 'jafa']) }}" class="nav-link {{ str_contains($currentRoute, 'marketing.submissions.monitoring') && request('program') === 'jafa' ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-line text-success"></i> Monitoring Proses JAFA
                </a>
                <hr style="margin: 15px 0; opacity: 0.2;">
                <div style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px; padding: 8px 20px; color: #6c757d; text-transform: uppercase;">
                    <i class="bi bi-journal-bookmark"></i> Pengelolaan Jurnal
                </div>
                @rolecap('marketing', 'kelola_jurnal')
                <a href="{{ route('marketing.journals.index') }}" class="nav-link {{ str_contains($currentRoute, 'marketing.journals') ? 'active' : '' }}">
                    <i class="bi bi-journal-text"></i> Data Jurnal
                </a>
                @endrolecap
                @rolecap('marketing', 'kelola_slot')
                <a href="{{ route('marketing.journal-slots.index') }}" class="nav-link {{ str_contains($currentRoute, 'marketing.journal-slots') ? 'active' : '' }}">
                    <i class="bi bi-calendar3"></i> Data Slot
                </a>
                @endrolecap
                <hr style="margin: 15px 0; opacity: 0.2;">
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
    @yield('scripts')
</body>
</html>
