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
            border: 1px solid #e9ecef;
        }
        
        .filter-section h6 {
            color: #667eea;
            font-weight: 600;
        }
        
        .filter-section .form-label {
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .filter-section .form-select,
        .filter-section .form-control {
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        
        .filter-section .form-select:focus,
        .filter-section .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .badge-info {
            background: linear-gradient(45deg, #667eea, #764ba2) !important;
        }
        
        #advancedFilterContent {
            border-top: 1px solid #e9ecef;
            padding-top: 20px;
            margin-top: 15px;
        }
        
        .table-section {
            background: white;
            border-radius: 15px;
            padding: 0;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-top: 25px;
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
            <p class="text-muted">Sistem Informasi Pencarian Dan Ketersediaan Slot Jurnal</p>
        </div>

        <!-- Search Section -->
        <div class="filter-section mb-3">
            <div class="d-flex align-items-center mb-3">
                <i class="bi bi-search me-2" style="font-size: 1.2rem; color: #667eea;"></i>
                <h6 class="mb-0 fw-bold">Pencarian</h6>
            </div>
            <form method="GET" action="{{ route('public.slot.info') }}" id="searchForm">
                <div class="input-group">
                    <input type="text" name="search" class="form-control form-control-lg" 
                           placeholder="Cari berdasarkan nama jurnal, bidang ilmu, penerbit, atau kode slot..." 
                           value="{{ request('search') }}">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>

        <!-- Basic Filter Section -->
        <div class="filter-section mb-3">
            <div class="d-flex align-items-center mb-3">
                <i class="bi bi-funnel me-2" style="font-size: 1.2rem; color: #667eea;"></i>
                <h6 class="mb-0 fw-bold">Filter Slot</h6>
            </div>
            <form method="GET" action="{{ route('public.slot.info') }}" id="basicFilterForm">
                <input type="hidden" name="search" value="{{ request('search') }}">
                
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">📜 Akreditasi</label>
                        <select name="indexasi" class="form-select" onchange="this.form.submit()">
                            <option value="">Semua Akreditasi</option>
                            @foreach($indexations as $idx)
                                <option value="{{ $idx }}" {{ request('indexasi') == $idx ? 'selected' : '' }}>
                                    {{ $idx }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">🎓 Bidang Ilmu</label>
                        <select name="kategori" class="form-select" onchange="this.form.submit()">
                            <option value="">Semua Bidang Ilmu</option>
                            @foreach($kategoriOptions as $kategori)
                                <option value="{{ $kategori }}" {{ request('kategori') == $kategori ? 'selected' : '' }}>
                                    {{ $kategori }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">📅 Tahun Terbit</label>
                        <select name="tahun" class="form-select" onchange="this.form.submit()">
                            <option value="">Semua Tahun</option>
                            @foreach($tahunOptions as $tahun)
                                <option value="{{ $tahun }}" {{ request('tahun') == $tahun ? 'selected' : '' }}>
                                    {{ $tahun }}{{ $tahun == date('Y') ? ' (Tahun Ini)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="w-100">
                            <button type="button" class="btn btn-primary w-100" onclick="this.form.submit()">
                                Terapkan
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="row g-3 mt-1">
                    <div class="col-md-9">
                        @if(request()->hasAny(['indexasi', 'kategori', 'tahun']))
                        <a href="{{ route('public.slot.info', request()->only('search')) }}" 
                           class="btn btn-outline-secondary btn-sm">
                            Reset
                        </a>
                        @endif
                    </div>
                    <div class="col-md-3 text-end">
                        <span class="small text-muted">
                            Menampilkan {{ $slots->count() }} dari {{ $slots->total() }} slot
                        </span>
                    </div>
                </div>
            </form>
        </div>

        <!-- Advanced Filter Section -->
        <div class="filter-section">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center">
                    <i class="bi bi-sliders me-2" style="font-size: 1.2rem; color: #667eea;"></i>
                    <h6 class="mb-0 fw-bold">Filter Lanjutan</h6>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleAdvancedFilter()">
                    <i class="bi bi-chevron-down" id="advancedToggleIcon"></i>
                </button>
            </div>
            
            <div id="advancedFilterContent" style="display: none;">
                <form method="GET" action="{{ route('public.slot.info') }}" id="advancedFilterForm">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="indexasi" value="{{ request('indexasi') }}">
                    <input type="hidden" name="kategori" value="{{ request('kategori') }}">
                    <input type="hidden" name="tahun" value="{{ request('tahun') }}">
                    
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small text-muted">🏷️ Jenis Jurnal</label>
                            <select name="jenis" class="form-select">
                                <option value="">Semua Jenis</option>
                                @foreach($jenisOptions as $jenis)
                                    <option value="{{ $jenis }}" {{ request('jenis') == $jenis ? 'selected' : '' }}>
                                        {{ $jenis }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted">🏢 Penerbit</label>
                            <select name="publisher" class="form-select">
                                <option value="">Semua Penerbit</option>
                                @foreach($publisherOptions as $publisher)
                                    <option value="{{ $publisher }}" {{ request('publisher') == $publisher ? 'selected' : '' }}>
                                        {{ strlen($publisher) > 30 ? substr($publisher, 0, 30) . '...' : $publisher }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted">📆 Bulan Terbit</label>
                            <select name="bulan" class="form-select">
                                <option value="">Semua Bulan</option>
                                @foreach($bulanOptions as $value => $label)
                                    <option value="{{ $value }}" {{ request('bulan') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted">📊 Status Slot</label>
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="tersedia" {{ request('status') == 'tersedia' ? 'selected' : '' }}>Slot Tersedia</option>
                                <option value="penuh" {{ request('status') == 'penuh' ? 'selected' : '' }}>Slot Penuh</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row g-3 mt-2">
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-primary">
                                Terapkan
                            </button>
                            @if(request()->hasAny(['jenis', 'publisher', 'bulan', 'status']))
                            <button type="button" class="btn btn-outline-secondary ms-2" 
                                    onclick="resetAdvancedFilter()">
                                Reset
                            </button>
                            @endif
                        </div>
                        <div class="col-md-6 text-end">
                            @if(request()->hasAny(['search', 'journal_id', 'indexasi', 'tahun', 'bulan', 'kategori', 'jenis', 'publisher', 'status']))
                            <span class="badge bg-info">
                                {{ collect(request()->only(['search', 'journal_id', 'indexasi', 'tahun', 'bulan', 'kategori', 'jenis', 'publisher', 'status']))->filter()->count() }} filter aktif
                            </span>
                            <a href="{{ route('public.slot.info') }}" class="btn btn-sm btn-outline-danger ms-2">
                                <i class="bi bi-x-circle"></i> Reset Semua
                            </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table Section -->
        <div class="table-section">
            <div class="table-responsive">
                <table class="table journal-table">
                    <thead>
                        <tr>
                            <th style="width: 3%;">No</th>
                            <th style="width: 22%;">Nama Jurnal</th>
                            <th style="width: 13%;">Penerbit</th>
                            <th style="width: 9%;">Kategori</th>
                            <th style="width: 11%;">Jenis</th>
                            <th style="width: 10%;">Akreditasi</th>
                            <th style="width: 9%;" class="text-center">Bulan</th>
                            <th style="width: 9%;" class="text-center">Tahun</th>
                            <th style="width: 9%;" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($slots as $index => $slot)
                        <tr>
                            <td class="text-center">{{ $slots->firstItem() + $index }}</td>
                            <td>
                                @if($slot->journalMaster && $slot->journalMaster->link_jurnal)
                                    <a href="{{ $slot->journalMaster->link_jurnal }}" target="_blank" class="text-decoration-none">
                                        <strong>{{ $slot->journalMaster->nama_jurnal }}</strong>
                                        <i class="bi bi-box-arrow-up-right ms-1"></i>
                                    </a>
                                @else
                                    <strong>{{ $slot->journalMaster->nama_jurnal ?? '-' }}</strong>
                                @endif
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
                                    $slotTersedia = $jumlahSlot - $slotTerpakai;
                                @endphp
                                @if($jumlahSlot > 0 && $slotTersedia > 0)
                                    <span class="badge bg-success">Tersedia</span>
                                    <br><small class="text-muted">{{ $slotTersedia }} dari {{ $jumlahSlot }} slot</small>
                                @elseif($jumlahSlot > 0 && $slotTersedia <= 0)
                                    <span class="badge bg-danger">Slot Penuh</span>
                                    <br><small class="text-muted">{{ $jumlahSlot }} slot</small>
                                @else
                                    <span class="badge bg-secondary">Tidak Ada Info</span>
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
        function toggleAdvancedFilter() {
            const content = document.getElementById('advancedFilterContent');
            const icon = document.getElementById('advancedToggleIcon');
            
            if (content.style.display === 'none' || content.style.display === '') {
                content.style.display = 'block';
                icon.className = 'bi bi-chevron-up';
            } else {
                content.style.display = 'none';
                icon.className = 'bi bi-chevron-down';
            }
        }
        
        function resetAdvancedFilter() {
            const form = document.getElementById('advancedFilterForm');
            const selects = form.querySelectorAll('select[name="jenis"], select[name="publisher"], select[name="bulan"], select[name="status"]');
            
            selects.forEach(select => {
                select.value = '';
            });
            
            form.submit();
        }
        
        // Auto-expand advanced filter if any advanced filters are active
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const advancedFilters = ['jenis', 'publisher', 'bulan', 'status'];
            const hasAdvancedFilter = advancedFilters.some(filter => urlParams.get(filter));
            
            if (hasAdvancedFilter) {
                document.getElementById('advancedFilterContent').style.display = 'block';
                document.getElementById('advancedToggleIcon').className = 'bi bi-chevron-up';
            }
        });
        
        // Auto-submit search form on Enter
        document.querySelector('input[name="search"]').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('searchForm').submit();
            }
        });
    </script>
</body>
</html>
