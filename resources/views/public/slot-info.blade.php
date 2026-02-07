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
        
        .main-content-wrapper {
            display: flex;
            gap: 25px;
            align-items: flex-start;
        }
        
        .sidebar {
            width: 320px;
            flex-shrink: 0;
        }
        
        .content-area {
            flex: 1;
            min-width: 0;
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
            border: 1px solid #e9ecef;
            margin-bottom: 20px;
        }
        
        .filter-section h6 {
            color: #667eea;
            font-weight: 600;
        }
        
        .filter-section .form-label {
            font-weight: 500;
            margin-bottom: 5px;
            color: #495057;
            font-size: 0.9rem;
        }
        
        .filter-section .form-select,
        .filter-section .form-control {
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        
        .filter-section .form-select:focus,
        .filter-section .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .badge-info {
            background: linear-gradient(45deg, #667eea, #764ba2) !important;
        }
        
        .filter-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-apply {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-weight: 600;
            flex: 1;
        }
        
        .btn-apply:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            color: white;
        }
        
        .btn-reset {
            background: #6c757d;
            border: none;
            color: white;
            flex: 1;
        }
        
        .btn-reset:hover {
            background: #5a6268;
            color: white;
        }
        
        .result-summary {
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        @media (max-width: 768px) {
            .main-content-wrapper {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
            }
            
            .journal-table {
                font-size: 0.75rem;
            }
            
            .journal-table th,
            .journal-table td {
                padding: 8px 4px;
            }
            
            .result-summary {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
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
            overflow-x: auto;
        }
        
        .journal-table {
            margin-bottom: 0;
            width: 100%;
            table-layout: fixed;
        }
        
        .journal-table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .journal-table thead th {
            border: none;
            padding: 15px 8px;
            font-weight: 600;
            font-size: 0.85rem;
            vertical-align: middle;
            word-wrap: break-word;
        }
        
        .journal-table tbody td {
            padding: 12px 8px;
            vertical-align: middle;
            font-size: 0.8rem;
            word-wrap: break-word;
            overflow-wrap: break-word;
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
            <p class="text-muted">Sistem Informasi Pencarian Dan Ketersediaan Slot Jurnal</p>
        </div>

        <!-- Stats Cards Section - Moved to top -->
        <div class="stats-section mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="stat-card">
                        <i class="bi bi-journal-bookmark text-primary"></i>
                        <h3>{{ number_format($stats['total_slots']) }}</h3>
                        <p class="mb-0" style="font-size: 0.9rem;">Total Slot</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="stat-card">
                        <i class="bi bi-check-circle text-success"></i>
                        <h3>{{ number_format($stats['slot_tersedia']) }}</h3>
                        <p class="mb-0" style="font-size: 0.9rem;">Slot Tersedia</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="stat-card">
                        <i class="bi bi-hourglass-split text-warning"></i>
                        <h3>{{ number_format($stats['slot_terpakai']) }}</h3>
                        <p class="mb-0" style="font-size: 0.9rem;">Slot Terpakai</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Wrapper -->
        <div class="main-content-wrapper">
            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Search Section -->
                <div class="filter-section">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-search me-2" style="font-size: 1.2rem; color: #667eea;"></i>
                        <h6 class="mb-0 fw-bold">Pencarian</h6>
                    </div>
                    <form method="GET" action="{{ route('public.slot.info') }}" id="searchForm">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Cari nama jurnal..." 
                               value="{{ request('search') }}">
                        <p class="form-text small mt-2 text-muted">
                            Cari berdasarkan nama jurnal, bidang ilmu, atau kata kunci
                        </p>
                    </form>
                </div>

                <!-- Filter Section -->
                <div class="filter-section">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-funnel me-2" style="font-size: 1.2rem; color: #667eea;"></i>
                        <h6 class="mb-0 fw-bold">Filter Jurnal</h6>
                    </div>
                    
                    <form method="GET" action="{{ route('public.slot.info') }}" id="filterForm">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        
                        <!-- Akreditasi -->
                        <div class="mb-3">
                            <label class="form-label">📜 Akreditasi</label>
                            <select name="indexasi" class="form-select">
                                <option value="">Semua Akreditasi</option>
                                @foreach($indexations as $idx)
                                    <option value="{{ $idx }}" {{ request('indexasi') == $idx ? 'selected' : '' }}>
                                        {{ $idx }} ({{ $filterCounts['indexasi'][$idx] ?? 0 }} slot)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Bidang Ilmu -->
                        <div class="mb-3">
                            <label class="form-label">🎓 Bidang Ilmu</label>
                            <select name="kategori" class="form-select">
                                <option value="">Semua Bidang</option>
                                @foreach($kategoriOptions as $kategori)
                                    <option value="{{ $kategori }}" {{ request('kategori') == $kategori ? 'selected' : '' }}>
                                        {{ $kategori }} ({{ $filterCounts['kategori'][$kategori] ?? 0 }} slot)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Jenis -->
                        <div class="mb-3">
                            <label class="form-label">🏷️ Jenis</label>
                            <select name="jenis" class="form-select">
                                <option value="">Semua Jenis</option>
                                @foreach($jenisOptions as $jenis)
                                    <option value="{{ $jenis }}" {{ request('jenis') == $jenis ? 'selected' : '' }}>
                                        {{ $jenis }} ({{ $filterCounts['jenis'][$jenis] ?? 0 }} slot)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tahun -->
                        <div class="mb-3">
                            <label class="form-label">📅 Tahun</label>
                            <select name="tahun" class="form-select">
                                <option value="">Semua Tahun</option>
                                @foreach($tahunOptions as $tahun)
                                    <option value="{{ $tahun }}" {{ request('tahun') == $tahun ? 'selected' : '' }}>
                                        {{ $tahun }} ({{ $filterCounts['tahun'][$tahun] ?? 0 }} slot)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Bulan -->
                        <div class="mb-3">
                            <label class="form-label">📆 Bulan</label>
                            <select name="bulan" class="form-select">
                                <option value="">Semua Bulan</option>
                                @foreach($bulanOptions as $key => $bulan)
                                    @if(isset($filterCounts['bulan'][$key]) && $filterCounts['bulan'][$key] > 0)
                                    <option value="{{ $key }}" {{ request('bulan') == $key ? 'selected' : '' }}>
                                        {{ $bulan }} ({{ $filterCounts['bulan'][$key] }} slot)
                                    </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="filter-buttons">
                            <button type="submit" class="btn btn-apply">
                                Terapkan
                            </button>
                            <a href="{{ route('public.slot.info') }}" class="btn btn-reset">
                                Reset
                            </a>
                        </div>
                        
                        <!-- Filter Summary -->
                        @if($slots->total() > 0)
                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                Menampilkan <strong>{{ $slots->total() }}</strong> jurnal
                            </small>
                        </div>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="content-area">
                <!-- Result Summary -->
                <div class="result-summary">
                    <div>
                        <span class="fw-bold">Menampilkan {{ $slots->firstItem() ?? 0 }}-{{ $slots->lastItem() ?? 0 }} dari {{ $slots->total() }}</span>
                    </div>
                    <div>
                        <label for="sortSelect" class="form-label mb-0 me-2">Urutkan:</label>
                        <select id="sortSelect" class="form-select form-select-sm" style="width: auto;">
                            <option>Terbaru</option>
                            <option>Nama A-Z</option>
                            <option>Slot Tersedia</option>
                        </select>
                    </div>                </div>
                <!-- Table Section -->
                <div class="table-section">
                    <div class="table-responsive">
                        <table class="table journal-table">
                            <thead>
                                <tr>
                                    <th style="width: 4%;">No</th>
                                    <th style="width: 25%;">Nama Jurnal</th>
                                    <th style="width: 18%;">Penerbit</th>
                                    <th style="width: 12%;">Kategori</th>
                                    <th style="width: 10%;">Jenis</th>
                                    <th style="width: 12%;">Akreditasi</th>
                                    <th style="width: 6%;">Bulan</th>
                                    <th style="width: 6%;">Tahun</th>
                                    <th style="width: 12%;">Status</th>
                                </tr>
                            </thead>
                    <tbody>
                        @forelse($slots as $index => $slot)
                        <tr>
                            <td class="text-center">{{ $slots->firstItem() + $index }}</td>
                            <td>
                                @if($slot->journalMaster && $slot->journalMaster->link_jurnal)
                                    <a href="{{ $slot->journalMaster->link_jurnal }}" target="_blank" class="text-decoration-none">
                                        <strong class="journal-name">{{ strlen($slot->journalMaster->nama_jurnal) > 40 ? substr($slot->journalMaster->nama_jurnal, 0, 40) . '...' : $slot->journalMaster->nama_jurnal }}</strong>
                                        <i class="bi bi-box-arrow-up-right ms-1"></i>
                                    </a>
                                @else
                                    @php $namaJurnal = $slot->journalMaster->nama_jurnal ?? '-'; @endphp
                                    <strong class="journal-name">{{ strlen($namaJurnal) > 40 ? substr($namaJurnal, 0, 40) . '...' : $namaJurnal }}</strong>
                                @endif
                                @if($slot->journalMaster && $slot->journalMaster->rumpun_ilmu)
                                    <br><small class="text-muted journal-info">{{ strlen($slot->journalMaster->rumpun_ilmu) > 30 ? substr($slot->journalMaster->rumpun_ilmu, 0, 30) . '...' : $slot->journalMaster->rumpun_ilmu }}</small>
                                @endif
                            </td>
                            <td>
                                @php $publisher = $slot->journalMaster->publisher ?? '-'; @endphp
                                <span title="{{ $publisher }}">
                                    {{ strlen($publisher) > 25 ? substr($publisher, 0, 25) . '...' : $publisher }}
                                </span>
                            </td>
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
                                <strong>{{ $slot->bulan ?? '-' }}</strong>
                            </td>
                            <td class="text-center">
                                <strong>{{ $slot->tahun ?? '-' }}</strong>
                            </td>
                            <td class="text-center">
                                @php
                                    // Ambil data dari field jumlah_slot dan slot_terpakai
                                    $jumlahSlot = $slot->jumlah_slot ?? 0;
                                    $slotTerpakai = $slot->slot_terpakai ?? 0;
                                    $slotTersedia = max(0, $jumlahSlot - $slotTerpakai); // Prevent negative values
                                @endphp
                                @if($jumlahSlot > 0 && $slotTersedia > 0)
                                    <span class="badge bg-success mb-1">Tersedia</span>
                                    <br><small class="text-muted">{{ $slotTersedia }}/{{ $jumlahSlot }}</small>
                                @elseif($jumlahSlot > 0 && $slotTersedia <= 0)
                                    <span class="badge bg-danger mb-1">Penuh</span>
                                    <br><small class="text-muted">{{ $jumlahSlot }} slot</small>
                                @else
                                    <span class="badge bg-secondary">N/A</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
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
            </div>
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
    
    <!-- Custom JavaScript -->
    <script>
        // Auto-submit search form on Enter
        document.querySelector('input[name="search"]').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('searchForm').submit();
            }
        });
        
        // Auto-submit search form on input change (with debounce)
        let searchTimeout;
        document.querySelector('input[name="search"]').addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                // Auto-submit after 500ms of no typing
                if (e.target.value.length > 2 || e.target.value.length === 0) {
                    document.getElementById('searchForm').submit();
                }
            }, 500);
        });
        
        // Auto-submit filter form on change
        document.querySelectorAll('#filterForm select').forEach(select => {
            select.addEventListener('change', function() {
                // Copy search value to filter form
                const searchValue = document.querySelector('#searchForm input[name="search"]').value;
                document.querySelector('#filterForm input[name="search"]').value = searchValue;
                document.getElementById('filterForm').submit();
            });
        });
        
        // Sort functionality
        document.getElementById('sortSelect').addEventListener('change', function() {
            // You can implement sorting logic here
            console.log('Sort by:', this.value);
        });
    </script>
</body>
</html>
