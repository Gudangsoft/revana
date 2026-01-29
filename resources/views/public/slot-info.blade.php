<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Sistem Informasi Pencarian Dan Ketersediaan Slot Jurnal SIPERA APJI. Temukan slot jurnal yang tersedia untuk publikasi artikel ilmiah Anda.">
    <meta name="keywords" content="info slot jurnal, ketersediaan slot, publikasi jurnal, SIPERA, APJI, slot jurnal tersedia, jurnal SINTA, jurnal nasional, jurnal internasional">
    <meta name="author" content="APJI - Asosiasi Penerbit Jurnal Indonesia">
    <meta name="robots" content="index, follow">
    <meta name="language" content="Indonesian">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="Info Slot Jurnal - SIPERA APJI">
    <meta property="og:description" content="Temukan slot jurnal yang tersedia untuk publikasi artikel ilmiah Anda di SIPERA APJI">
    <meta property="og:site_name" content="SIPERA - APJI">
    <meta property="og:url" content="{{ url()->current() }}">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Info Slot Jurnal - SIPERA APJI">
    <meta name="twitter:description" content="Temukan slot jurnal yang tersedia untuk publikasi artikel ilmiah Anda di SIPERA APJI">
    
    <title>Info Slot Jurnal - SIPERA</title>
    
    <!-- Favicon -->
    @if(isset($settings['favicon']) && $settings['favicon'])
    <link rel="icon" href="{{ asset('storage/' . $settings['favicon']) }}" type="image/x-icon">
    @endif
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .container-main {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        
        .header-section h1 {
            color: #667eea;
            font-weight: bold;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .stats-section {
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            height: 100%;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .stat-card h3 {
            font-size: 2rem;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .filter-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
        }
        
        .table-section {
            background: white;
            border-radius: 15px;
            padding: 0;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .table-responsive {
            border-radius: 15px;
        }
        
        .journal-table {
            margin-bottom: 0;
        }
        
        .journal-table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .journal-table thead th {
            border: none;
            padding: 15px 10px;
            font-weight: 600;
            font-size: 0.9rem;
            vertical-align: middle;
        }
        
        .journal-table tbody td {
            padding: 12px 10px;
            vertical-align: middle;
            font-size: 0.85rem;
        }
        
        .journal-table tbody tr {
            border-bottom: 1px solid #e9ecef;
        }
        
        .journal-table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .badge-indexasi {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-nasional {
            background: #28a745;
            color: white;
        }
        
        .badge-sinta4 {
            background: #17a2b8;
            color: white;
        }
        
        .badge-sinta5 {
            background: #ffc107;
            color: #000;
        }
        
        .badge-internasional {
            background: #dc3545;
            color: white;
        }
        
        .slot-badge {
            background: #667eea;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
        }
        
        .journal-name {
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
        }
        
        .journal-info {
            color: #666;
            font-size: 0.75rem;
            margin-top: 3px;
        }
        
        .login-section {
            text-align: center;
            margin-top: 25px;
        }
        
        .login-section a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 12px 30px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .login-section a:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .footer-section {
            text-align: center;
            margin-top: 25px;
            color: white;
        }
        
        .pagination {
            margin: 20px;
        }
        
        .no-image {
            width: 60px;
            height: 60px;
            background: #e9ecef;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container container-main">
        <!-- Header -->
        <div class="header-section">
            @if(isset($settings['logo']) && $settings['logo'])
                <img src="{{ asset('storage/' . $settings['logo']) }}" alt="Logo" style="max-height: 80px; margin-bottom: 15px;">
            @else
                <h1><i class="bi bi-journal-bookmark-fill"></i> {{ $settings['app_name'] ?? 'SIPERA' }}</h1>
            @endif
            @if(isset($settings['logo']) && $settings['logo'])
                <h1 class="mt-2">{{ $settings['app_name'] ?? 'SIPERA' }}</h1>
            @endif
            <p class="lead mb-1">Sistem Insentif dan Penghargaan Reviewer APJI</p>
            <p class="text-muted">Sistem Informasi Pencarian Dan Ketersediaan Slot Jurnal</p>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <h5 class="mb-3"><i class="bi bi-funnel"></i> Cari Slot Jurnal</h5>
            <form method="GET" action="{{ route('public.slot.info') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama jurnal..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="journal_id" class="form-select">
                            <option value="">-- Semua Jurnal --</option>
                            @foreach($journals as $journal)
                                <option value="{{ $journal->id }}" {{ request('journal_id') == $journal->id ? 'selected' : '' }}>
                                    {{ $journal->nama_jurnal }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="indexasi" class="form-select">
                            <option value="">-- Indexasi --</option>
                            @foreach($indexations as $idx)
                                <option value="{{ $idx }}" {{ request('indexasi') == $idx ? 'selected' : '' }}>
                                    {{ $idx }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="tahun" class="form-select">
                            <option value="">-- Tahun --</option>
                            @foreach($tahunOptions as $tahun)
                                <option value="{{ $tahun }}" {{ request('tahun') == $tahun ? 'selected' : '' }}>
                                    {{ $tahun }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="bulan" class="form-select">
                            <option value="">-- Bulan --</option>
                            @foreach($bulanOptions as $value => $label)
                                <option value="{{ $value }}" {{ request('bulan') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
                @if(request()->hasAny(['search', 'journal_id', 'indexasi', 'tahun', 'bulan']))
                <div class="mt-2">
                    <a href="{{ route('public.slot.info') }}" class="btn btn-sm btn-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset Filter
                    </a>
                    <span class="badge bg-info ms-2">
                        {{ collect(request()->only(['search', 'journal_id', 'indexasi', 'tahun', 'bulan']))->filter()->count() }} filter aktif
                    </span>
                </div>
                @endif
            </form>
        </div>

        <!-- Table Section -->
        <div class="table-section">
            <div class="table-responsive">
                <table class="table journal-table">
                    <thead>
                        <tr>
                            <th style="width: 3%;">No</th>
                            <th style="width: 10%;">Kode Slot</th>
                            <th style="width: 20%;">Nama Jurnal</th>
                            <th style="width: 12%;">Penerbit</th>
                            <th style="width: 8%;">Kategori</th>
                            <th style="width: 10%;">Jenis</th>
                            <th style="width: 9%;">Akreditasi</th>
                            <th style="width: 6%;" class="text-center">Volume</th>
                            <th style="width: 6%;" class="text-center">Nomor</th>
                            <th style="width: 8%;" class="text-center">Bulan</th>
                            <th style="width: 8%;" class="text-center">Tahun</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($slots as $index => $slot)
                        <tr>
                            <td class="text-center">{{ $slots->firstItem() + $index }}</td>
                            <td>
                                <strong class="text-primary">{{ $slot->kode_slot }}</strong>
                            </td>
                            <td>
                                <strong>{{ $slot->journalMaster->nama_jurnal ?? '-' }}</strong>
                                @if($slot->journalMaster && $slot->journalMaster->rumpun_ilmu)
                                    <br><small class="text-muted">{{ $slot->journalMaster->rumpun_ilmu }}</small>
                                @endif
                            </td>
                            <td>{{ $slot->journalMaster->publisher ?? '-' }}</td>
                            <td>
                                @if($slot->journalMaster && $slot->journalMaster->kategori)
                                    <span class="badge bg-info">{{ $slot->journalMaster->kategori }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($slot->journalMaster && $slot->journalMaster->jenis_jurnal)
                                    <span class="badge bg-primary">{{ $slot->journalMaster->jenis_jurnal }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($slot->journalMaster && $slot->journalMaster->accreditation)
                                    @php
                                        $accreditation = $slot->journalMaster->accreditation;
                                        $badgeClass = 'bg-success';
                                        if (str_contains($accreditation, 'SINTA 4')) $badgeClass = 'bg-info';
                                        elseif (str_contains($accreditation, 'SINTA 5')) $badgeClass = 'bg-warning';
                                        elseif (str_contains($accreditation, 'INTERNASIONAL')) $badgeClass = 'bg-danger';
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ $accreditation }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <strong>{{ $slot->volume ?? '-' }}</strong>
                            </td>
                            <td class="text-center">
                                <strong>{{ $slot->nomor ?? '-' }}</strong>
                            </td>
                            <td class="text-center">
                                <strong>{{ $slot->bulan ?? '-' }}</strong>
                            </td>
                            <td class="text-center">
                                <strong>{{ $slot->tahun ?? '-' }}</strong>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="mt-3 text-muted">Belum ada slot jurnal yang tersedia</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($slots->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 pb-3">
                <div class="text-muted">
                    Menampilkan {{ $slots->firstItem() ?? 0 }} - {{ $slots->lastItem() ?? 0 }} dari {{ $slots->total() }} slot
                </div>
                <div>
                    {{ $slots->links() }}
                </div>
            </div>
            @endif
        </div>

        <!-- Login Link -->
        <div class="login-section">
            <a href="{{ route('login') }}" target="_blank">
                <i class="bi bi-box-arrow-in-right"></i> Login ke Sistem SIPERA
            </a>
        </div>

        <!-- Footer -->
        <div class="footer-section">
            <p class="mb-1">
                <small>
                    &copy; {{ date('Y') }} SIPERA - Sistem Insentif dan Penghargaan Reviewer APJI
                </small>
            </p>
            <p>
                <small>
                    Jl. Watunganten I No.1, Karangrawa, Batursari, Kec. Mranggen, Kabupaten Demak, Jawa Tengah 59567
                </small>
            </p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
