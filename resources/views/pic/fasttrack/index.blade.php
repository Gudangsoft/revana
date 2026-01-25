<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Fasttrack - {{ $appSettings['app_name'] ?? 'SIPERA' }}</title>
    @if(isset($appSettings['favicon']) && $appSettings['favicon'])
    <link rel="icon" href="{{ asset('storage/' . $appSettings['favicon']) }}" type="image/x-icon">
    @endif
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar-pic {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            border-left-color: #667eea;
        }
        .sidebar .nav-link.active {
            background: #eef2ff;
            border-left-color: #667eea;
            color: #667eea;
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
        
        /* Sticky Table Styles for Monitoring */
        .monitoring-scroll-wrapper {
            overflow-x: auto;
            overflow-y: visible;
            max-height: 70vh;
            scrollbar-width: thin;
            scrollbar-color: #6c757d #dee2e6;
        }

        .monitoring-scroll-wrapper::-webkit-scrollbar {
            height: 14px;
            width: 14px;
        }

        .monitoring-scroll-wrapper::-webkit-scrollbar-track {
            background: #dee2e6;
            border-radius: 7px;
        }

        .monitoring-scroll-wrapper::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #6c757d, #495057);
            border-radius: 7px;
            border: 2px solid #dee2e6;
        }

        .monitoring-scroll-wrapper::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #495057, #343a40);
        }

        .monitoring-scroll-wrapper::-webkit-scrollbar-corner {
            background: #dee2e6;
        }

        .table-monitoring {
            border-collapse: collapse;
            border-spacing: 0;
            font-size: 0.8rem;
        }

        .table-monitoring thead th {
            position: sticky;
            top: 0;
            z-index: 3;
            background: #212529 !important;
            color: white !important;
            border: 1px solid #212529 !important;
            white-space: nowrap;
            padding: 6px 8px;
        }

        .table-monitoring thead tr:nth-child(2) th {
            top: 32px;
            background: #343a40 !important;
            color: white !important;
            border: 1px solid #343a40 !important;
        }

        .table-monitoring thead th.bg-info,
        .table-monitoring thead th.bg-warning,
        .table-monitoring thead th.bg-primary,
        .table-monitoring thead th.bg-success {
            color: white !important;
            border-color: #212529 !important;
        }

        .table-monitoring th.sticky-first,
        .table-monitoring td.sticky-first {
            position: sticky;
            left: 0;
            z-index: 2;
            background: #fff;
            min-width: 120px;
            box-shadow: 3px 0 6px -3px rgba(0,0,0,0.15);
        }

        .table-monitoring thead th.sticky-first {
            z-index: 5;
            background: #212529 !important;
        }

        .table-monitoring th.sticky-second,
        .table-monitoring td.sticky-second {
            position: sticky;
            left: 120px;
            z-index: 2;
            background: #fff;
            min-width: 100px;
            box-shadow: 3px 0 6px -3px rgba(0,0,0,0.15);
        }

        .table-monitoring thead th.sticky-second {
            z-index: 5;
            background: #212529 !important;
        }

        .table-monitoring tbody td {
            white-space: nowrap;
            padding: 5px 8px;
            border: 1px solid #dee2e6;
        }

        .table-monitoring tbody tr:hover td {
            background-color: #e8f4fd !important;
        }

        .table-monitoring tbody tr:hover td.sticky-first,
        .table-monitoring tbody tr:hover td.sticky-second {
            background-color: #e8f4fd !important;
        }

        .table-monitoring tbody tr:nth-child(even) td {
            background-color: #f8f9fa;
        }

        .table-monitoring tbody tr:nth-child(even) td.sticky-first,
        .table-monitoring tbody tr:nth-child(even) td.sticky-second {
            background-color: #f8f9fa;
        }

        /* Scroll controls */
        .scroll-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding: 8px 12px;
            background: #f8f9fa;
            border-radius: 6px;
        }

        .scroll-nav-btn {
            padding: 6px 12px;
            border: 1px solid #dee2e6;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .scroll-nav-btn:hover {
            background: #e9ecef;
            border-color: #adb5bd;
        }

        .scroll-nav-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .scroll-position-indicator {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .scroll-position-bar {
            width: 200px;
            height: 6px;
            background: #dee2e6;
            border-radius: 3px;
            overflow: hidden;
        }

        .scroll-position-fill {
            height: 100%;
            background: linear-gradient(90deg, #ffc107, #fd7e14);
            border-radius: 3px;
            transition: width 0.1s;
        }

        .quick-nav {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
        }

        .quick-nav-btn {
            padding: 4px 8px;
            font-size: 0.7rem;
            border: 1px solid #dee2e6;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .quick-nav-btn:hover {
            background: #e9ecef;
        }

        .quick-nav-btn.active {
            background: #ffc107;
            color: #000;
            border-color: #ffc107;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-pic">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('pic.author.dashboard') }}">
                @if(isset($appSettings['logo']) && $appSettings['logo'])
                    <img src="{{ asset('storage/' . $appSettings['logo']) }}" alt="Logo" style="max-height: 30px;">
                @else
                    <i class="bi bi-person-badge"></i> PIC Portal
                @endif
            </a>
            <div class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
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
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar">
                @php $currentRoute = Route::currentRouteName(); @endphp
                <a href="{{ route('pic.author.dashboard') }}" class="nav-link {{ $currentRoute == 'pic.author.dashboard' ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a href="{{ route('pic.submissions.monitoring') }}" class="nav-link {{ str_contains($currentRoute, 'pic.submissions.monitoring') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-line"></i> Monitoring Artikel
                </a>
                <hr style="margin: 15px 0; opacity: 0.2;">
                <div style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px; padding: 8px 20px; color: #6c757d; text-transform: uppercase;">
                    <i class="bi bi-lightning-charge text-warning"></i> Fasttrack
                </div>
                <a href="{{ route('pic.fasttrack.index') }}" class="nav-link {{ str_contains($currentRoute, 'pic.fasttrack.index') ? 'active' : '' }}">
                    <i class="bi bi-lightning-charge text-warning"></i> Data Fasttrack
                </a>
                <a href="{{ route('pic.fasttrack.monitoring') }}" class="nav-link {{ str_contains($currentRoute, 'pic.fasttrack.monitoring') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart text-warning"></i> Monitoring Fasttrack
                </a>
                <hr style="margin: 15px 0; opacity: 0.2;">
                <div style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px; padding: 8px 20px; color: #6c757d; text-transform: uppercase;">
                    <i class="bi bi-journal-bookmark"></i> Pengelolaan Jurnal
                </div>
                <a href="{{ route('pic.journal-slots.monitoring') }}" class="nav-link {{ str_contains($currentRoute, 'pic.journal-slots') ? 'active' : '' }}">
                    <i class="bi bi-calendar3"></i> Monitoring Slot
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

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">
                        <i class="bi bi-lightning-charge text-warning"></i> Monitoring Fasttrack
                    </h4>
                    <div>
                        <a href="{{ route('pic.fasttrack.create') }}" class="btn btn-warning">
                            <i class="bi bi-plus-circle"></i> Input Fasttrack
                        </a>
                        <span class="badge bg-warning text-dark fs-6 ms-2">Total: {{ $submissions->total() }} artikel</span>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="alert alert-warning mb-3">
                    <i class="bi bi-lightning-charge"></i> <strong>Fasttrack</strong> = Artikel sudah publish, langsung tercatat tanpa proses workflow.
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-warning text-dark">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small>Total Fasttrack</small>
                                        <h4 class="mb-0">{{ $submissions->total() }}</h4>
                                    </div>
                                    <i class="bi bi-lightning-charge fs-2 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small>Published</small>
                                        <h4 class="mb-0">{{ $submissions->total() }}</h4>
                                    </div>
                                    <i class="bi bi-check-circle fs-2 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small>Proses Langsung</small>
                                        <h4 class="mb-0">100%</h4>
                                    </div>
                                    <i class="bi bi-speedometer2 fs-2 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter -->
                <div class="card mb-3">
                    <div class="card-body py-2">
                        <form method="GET" class="row g-2 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label small mb-1">Tanggal Dari</label>
                                <input type="date" name="tanggal_dari" class="form-control form-control-sm" value="{{ request('tanggal_dari') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small mb-1">Tanggal Sampai</label>
                                <input type="date" name="tanggal_sampai" class="form-control form-control-sm" value="{{ request('tanggal_sampai') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Cari</label>
                                <input type="text" name="search" class="form-control form-control-sm" placeholder="Kode/Judul/Penulis..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-4">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-search"></i> Filter
                                    </button>
                                    <a href="{{ route('pic.fasttrack.index') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-clockwise"></i> Refresh
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Table Card -->
                <div class="card">
                    <div class="card-body p-2">
                        <!-- Scroll Controls -->
                        <div class="scroll-controls">
                            <div class="d-flex align-items-center gap-3">
                                <button type="button" class="scroll-nav-btn" id="scrollStartBtn" title="Ke Awal">
                                    <i class="bi bi-chevron-bar-left"></i>
                                </button>
                                <button type="button" class="scroll-nav-btn" id="scrollLeftBtn" title="Scroll Kiri">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                                <div class="scroll-position-indicator">
                                    <div class="scroll-position-bar">
                                        <div class="scroll-position-fill" id="scrollPositionFill" style="width: 0%"></div>
                                    </div>
                                    <small class="text-muted" id="scrollPositionText">0%</small>
                                </div>
                                <button type="button" class="scroll-nav-btn" id="scrollRightBtn" title="Scroll Kanan">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                                <button type="button" class="scroll-nav-btn" id="scrollEndBtn" title="Ke Akhir">
                                    <i class="bi bi-chevron-bar-right"></i>
                                </button>
                            </div>
                            <div class="quick-nav">
                                <span class="text-muted me-2" style="font-size: 0.75rem;">Lompat ke:</span>
                                <button type="button" class="quick-nav-btn" data-target="submit">Submit</button>
                                <button type="button" class="quick-nav-btn" data-target="editor1">Editor1</button>
                                <button type="button" class="quick-nav-btn" data-target="author1">Author1</button>
                                <button type="button" class="quick-nav-btn" data-target="editor2">Editor2</button>
                                <button type="button" class="quick-nav-btn" data-target="reviewer1">Reviewer1</button>
                                <button type="button" class="quick-nav-btn" data-target="reviewer2">Reviewer2</button>
                                <button type="button" class="quick-nav-btn" data-target="editor3">Editor3</button>
                                <button type="button" class="quick-nav-btn" data-target="author2">Author2</button>
                                <button type="button" class="quick-nav-btn" data-target="production">Production</button>
                            </div>
                        </div>
                        
                        <div class="monitoring-scroll-wrapper" id="monitoringScrollWrapper">
                            <table class="table table-monitoring table-bordered mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th rowspan="2" class="sticky-first text-center">Kode Submit</th>
                                        <th rowspan="2" class="sticky-second">ID Artikel</th>
                                        <th rowspan="2">Judul</th>
                                        <th rowspan="2">Link</th>
                                        <th rowspan="2">Penulis</th>
                                        <th rowspan="2">No HP</th>
                                        <th colspan="4" class="text-center bg-info" data-section="submit">Author Access</th>
                                        <th colspan="3" class="text-center bg-warning" data-section="editor1">Editor 1</th>
                                        <th colspan="2" class="text-center bg-info" data-section="author1">Author 1</th>
                                        <th colspan="2" class="text-center bg-warning" data-section="editor2">Editor 2</th>
                                        <th colspan="4" class="text-center bg-primary" data-section="reviewer1">Reviewer 1</th>
                                        <th colspan="4" class="text-center bg-primary" data-section="reviewer2">Reviewer 2</th>
                                        <th colspan="2" class="text-center bg-warning" data-section="editor3">Editor 3</th>
                                        <th colspan="2" class="text-center bg-info" data-section="author2">Author 2</th>
                                        <th colspan="3" class="text-center bg-success" data-section="production">Production</th>
                                        <th rowspan="2">Aksi</th>
                                    </tr>
                                    <tr>
                                        <th class="bg-info">Marketing</th>
                                        <th class="bg-info">Petugas</th>
                                        <th class="bg-info">User</th>
                                        <th class="bg-info">Pass</th>
                                        <th class="bg-warning">Petugas</th>
                                        <th class="bg-warning">User/Pass</th>
                                        <th class="bg-warning">Valid</th>
                                        <th class="bg-info">Petugas</th>
                                        <th class="bg-info">Valid</th>
                                        <th class="bg-warning">Petugas</th>
                                        <th class="bg-warning">Valid</th>
                                        <th class="bg-primary">Petugas</th>
                                        <th class="bg-primary">User/Pass</th>
                                        <th class="bg-primary">Catatan</th>
                                        <th class="bg-primary">Valid</th>
                                        <th class="bg-primary">Petugas</th>
                                        <th class="bg-primary">User/Pass</th>
                                        <th class="bg-primary">Catatan</th>
                                        <th class="bg-primary">Valid</th>
                                        <th class="bg-warning">Petugas</th>
                                        <th class="bg-warning">Valid</th>
                                        <th class="bg-info">Petugas</th>
                                        <th class="bg-info">Valid</th>
                                        <th class="bg-success">Petugas</th>
                                        <th class="bg-success">Link</th>
                                        <th class="bg-success">Valid</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($submissions as $s)
                                    <tr>
                                        <td class="sticky-first">
                                            <a href="{{ route('pic.fasttrack.show', $s) }}" class="text-decoration-none">
                                                <code class="text-warning">{{ $s->kode_submit }}</code>
                                            </a>
                                            <span class="badge bg-warning text-dark ms-1"><i class="bi bi-lightning-charge"></i> FT</span>
                                            <br><span class="badge bg-success mt-1"><i class="bi bi-check-circle-fill"></i> SELESAI</span>
                                        </td>
                                        <td class="sticky-second">{{ $s->id_artikel ?? '-' }}</td>
                                        <td title="{{ $s->judul_artikel }}">{{ Str::limit($s->judul_artikel, 20) }}</td>
                                        <td class="text-center">
                                            @if($s->link_artikel)
                                                <a href="{{ $s->link_artikel }}" target="_blank"><i class="bi bi-link-45deg"></i></a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ Str::limit($s->nama_penulis, 12) }}</td>
                                        <td>
                                            @if($s->no_hp_penulis)
                                                @php
                                                    $waNumber = preg_replace('/[^0-9]/', '', $s->no_hp_penulis);
                                                    if (substr($waNumber, 0, 1) === '0') {
                                                        $waNumber = '62' . substr($waNumber, 1);
                                                    }
                                                    $waUrl = "https://wa.me/{$waNumber}";
                                                @endphp
                                                <a href="{{ $waUrl }}" target="_blank" class="btn btn-success btn-sm" style="padding: 2px 6px; font-size: 0.7rem;">
                                                    <i class="bi bi-whatsapp"></i>
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        
                                        <!-- Author Access -->
                                        <td>{{ $s->marketing->name ?? '-' }}</td>
                                        <td>{{ $s->petugasSubmit->name ?? ($s->marketing->name ?? '-') }}</td>
                                        <td><code>{{ $s->username_author ?? '-' }}</code></td>
                                        <td><code>{{ $s->password_author ?? '-' }}</code></td>
                                        
                                        <!-- Editor 1 -->
                                        <td>{{ $s->petugasEditor1->name ?? '-' }}</td>
                                        <td>@if($s->username_editor)<code>{{ $s->username_editor }}/{{ $s->password_editor ?? '-' }}</code>@else - @endif</td>
                                        <td class="text-center">{!! $s->editor1_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                        
                                        <!-- Author 1 -->
                                        <td>{{ $s->petugasAuthor1->name ?? '-' }}</td>
                                        <td class="text-center">{!! $s->author1_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                        
                                        <!-- Editor 2 -->
                                        <td>{{ $s->petugasEditor2->name ?? '-' }}</td>
                                        <td class="text-center">{!! $s->editor2_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                        
                                        <!-- Reviewer 1 -->
                                        <td>{{ $s->petugasReviewer1->name ?? '-' }}</td>
                                        <td>@if($s->username_reviewer1)<code>{{ $s->username_reviewer1 }}/{{ $s->password_reviewer1 ?? '-' }}</code>@else - @endif</td>
                                        <td title="{{ $s->catatan_reviewer1 }}">{{ Str::limit($s->catatan_reviewer1, 10) ?? '-' }}</td>
                                        <td class="text-center">{!! $s->reviewer1_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                        
                                        <!-- Reviewer 2 -->
                                        <td>{{ $s->petugasReviewer2->name ?? '-' }}</td>
                                        <td>@if($s->username_reviewer2)<code>{{ $s->username_reviewer2 }}/{{ $s->password_reviewer2 ?? '-' }}</code>@else - @endif</td>
                                        <td title="{{ $s->catatan_reviewer2 }}">{{ Str::limit($s->catatan_reviewer2, 10) ?? '-' }}</td>
                                        <td class="text-center">{!! $s->reviewer2_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                        
                                        <!-- Editor 3 -->
                                        <td>{{ $s->petugasEditor3->name ?? '-' }}</td>
                                        <td class="text-center">{!! $s->editor3_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                        
                                        <!-- Author 2 -->
                                        <td>{{ $s->petugasAuthor2->name ?? '-' }}</td>
                                        <td class="text-center">{!! $s->author2_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                        
                                        <!-- Production -->
                                        <td>{{ $s->petugasProduction->name ?? '-' }}</td>
                                        <td>
                                            @if($s->link_publish)
                                                <a href="{{ $s->link_publish }}" target="_blank" class="btn btn-sm btn-success" style="padding: 2px 6px; font-size: 0.7rem;">
                                                    <i class="bi bi-box-arrow-up-right"></i>
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-center"><i class="bi bi-check-circle-fill text-success"></i></td>
                                        
                                        <td class="text-center">
                                            <a href="{{ route('pic.fasttrack.show', $s) }}" class="btn btn-info btn-sm">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="32" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox display-6"></i>
                                            <p class="mt-2">Belum ada data fasttrack</p>
                                            <a href="{{ route('pic.fasttrack.create') }}" class="btn btn-warning">
                                                <i class="bi bi-plus-circle"></i> Input Fasttrack Pertama
                                            </a>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    {{ $submissions->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const wrapper = document.getElementById('monitoringScrollWrapper');
        const positionFill = document.getElementById('scrollPositionFill');
        const positionText = document.getElementById('scrollPositionText');
        const scrollLeftBtn = document.getElementById('scrollLeftBtn');
        const scrollRightBtn = document.getElementById('scrollRightBtn');
        const scrollStartBtn = document.getElementById('scrollStartBtn');
        const scrollEndBtn = document.getElementById('scrollEndBtn');
        
        const columnPositions = {
            'submit': 0,
            'editor1': 600,
            'author1': 850,
            'editor2': 1000,
            'reviewer1': 1150,
            'reviewer2': 1500,
            'editor3': 1850,
            'author2': 2000,
            'production': 2150
        };
        
        function updateScrollPosition() {
            const scrollLeft = wrapper.scrollLeft;
            const scrollWidth = wrapper.scrollWidth - wrapper.clientWidth;
            const progress = scrollWidth > 0 ? (scrollLeft / scrollWidth) * 100 : 0;
            positionFill.style.width = progress + '%';
            positionText.textContent = Math.round(progress) + '%';
            
            scrollStartBtn.disabled = scrollLeft <= 0;
            scrollLeftBtn.disabled = scrollLeft <= 0;
            scrollRightBtn.disabled = scrollLeft >= scrollWidth;
            scrollEndBtn.disabled = scrollLeft >= scrollWidth;
            
            document.querySelectorAll('.quick-nav-btn').forEach(btn => {
                btn.classList.remove('active');
            });
        }
        
        wrapper.addEventListener('scroll', updateScrollPosition);
        
        const scrollAmount = 400;
        
        scrollLeftBtn.addEventListener('click', () => {
            wrapper.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        });
        
        scrollRightBtn.addEventListener('click', () => {
            wrapper.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        });
        
        scrollStartBtn.addEventListener('click', () => {
            wrapper.scrollTo({ left: 0, behavior: 'smooth' });
        });
        
        scrollEndBtn.addEventListener('click', () => {
            wrapper.scrollTo({ left: wrapper.scrollWidth, behavior: 'smooth' });
        });
        
        document.querySelectorAll('.quick-nav-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const target = this.dataset.target;
                const position = columnPositions[target] || 0;
                
                wrapper.scrollTo({ left: position, behavior: 'smooth' });
                
                document.querySelectorAll('.quick-nav-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });
        
        wrapper.setAttribute('tabindex', '0');
        wrapper.addEventListener('keydown', function(e) {
            switch(e.key) {
                case 'ArrowLeft':
                    wrapper.scrollBy({ left: -100, behavior: 'smooth' });
                    break;
                case 'ArrowRight':
                    wrapper.scrollBy({ left: 100, behavior: 'smooth' });
                    break;
                case 'Home':
                    wrapper.scrollTo({ left: 0, behavior: 'smooth' });
                    break;
                case 'End':
                    wrapper.scrollTo({ left: wrapper.scrollWidth, behavior: 'smooth' });
                    break;
            }
        });
        
        updateScrollPosition();
    });
    </script>
</body>
</html>
