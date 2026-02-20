<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Cari dan cek ketersediaan slot jurnal berdasarkan Kode LOA (Letter of Acceptance). Sistem pencarian LOA jurnal SIPERA APJI secara online dan gratis.">
    <meta name="keywords" content="cari LOA, pencarian LOA, kode LOA jurnal, Letter of Acceptance, slot jurnal, ketersediaan jurnal, SIPERA, APJI, jurnal Indonesia, cek LOA online, status jurnal, publikasi ilmiah">
    <meta name="author" content="APJI - Asosiasi Penerbit Jurnal Indonesia">
    <meta name="robots" content="index, follow">
    <meta name="language" content="Indonesian">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="Pencarian Kode LOA Jurnal - SIPERA APJI">
    <meta property="og:description" content="Cari dan cek ketersediaan slot jurnal berdasarkan Kode LOA secara online dan gratis">
    <meta property="og:site_name" content="SIPERA - APJI">
    <meta property="og:url" content="{{ url()->current() }}">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Pencarian Kode LOA Jurnal - SIPERA APJI">
    <meta name="twitter:description" content="Cari dan cek ketersediaan slot jurnal berdasarkan Kode LOA secara online dan gratis">
    
    <title>Pencarian Kode LOA - SIPERA</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        
        .search-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo-section h1 {
            color: #667eea;
            font-weight: bold;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .logo-section p {
            color: #666;
            font-size: 1.1rem;
        }
        
        .search-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            height: 100%;
        }
        
        .stat-card i {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        
        .stat-card h3 {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-card p {
            color: #666;
            margin: 0;
        }
        
        .result-table {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        
        .btn-search {
            padding: 12px 30px;
            font-size: 1.1rem;
            border-radius: 10px;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .login-link a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .login-link a:hover {
            background: rgba(255, 255, 255, 0.3);
        }
    </style>
</head>
<body>
    <div class="container search-container">
        <div class="header-card">
            <div class="logo-section">
                <h1><i class="bi bi-journal-bookmark-fill"></i> SIPERA</h1>
                <p class="text-muted">Pencarian Kode LOA (Letter of Acceptance) Jurnal</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <i class="bi bi-calendar-range text-primary"></i>
                    <h3 class="text-primary">{{ $stats['total_slots'] }}</h3>
                    <p>Total Slot Jurnal</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <i class="bi bi-check-circle text-success"></i>
                    <h3 class="text-success">{{ $stats['available_slots'] }}</h3>
                    <p>Slot Tersedia</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <i class="bi bi-x-circle text-danger"></i>
                    <h3 class="text-danger">{{ $stats['full_slots'] }}</h3>
                    <p>Slot Penuh</p>
                </div>
            </div>
        </div>

        <!-- Search Form -->
        <div class="search-card">
            <h4 class="mb-4"><i class="bi bi-search"></i> Cari Kode LOA</h4>
            <form method="GET" action="{{ route('public.loa.search') }}">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">
                            <i class="bi bi-tag"></i> Kode LOA
                        </label>
                        <input type="text" 
                               name="search_loa" 
                               class="form-control form-control-lg" 
                               placeholder="Contoh: LOA-2024-001"
                               value="{{ request('search_loa') }}"
                               required
                               autofocus>
                        <small class="text-muted">Masukkan kode LOA yang ingin dicari</small>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-bold">
                            <i class="bi bi-journal"></i> Filter Jurnal (Opsional)
                        </label>
                        <select name="journal_id" class="form-select form-select-lg">
                            <option value="">-- Semua Jurnal --</option>
                            @foreach($journals as $journal)
                                <option value="{{ $journal->id }}" {{ request('journal_id') == $journal->id ? 'selected' : '' }}>
                                    {{ $journal->nama_jurnal }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-bold">
                            <i class="bi bi-funnel"></i> Status (Opsional)
                        </label>
                        <select name="status" class="form-select form-select-lg">
                            <option value="">-- Semua Status --</option>
                            <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Tersedia</option>
                            <option value="full" {{ request('status') == 'full' ? 'selected' : '' }}>Penuh</option>
                        </select>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary btn-search">
                        <i class="bi bi-search"></i> Cari Sekarang
                    </button>
                    <a href="{{ route('public.loa.search') }}" class="btn btn-secondary btn-search">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Search Results -->
        @if($searchPerformed)
        <div class="result-table">
            @if($results && $results->count() > 0)
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i> 
                Ditemukan <strong>{{ $results->total() }}</strong> hasil untuk pencarian 
                @if(request('search_loa'))
                    <strong>"{{ request('search_loa') }}"</strong>
                @endif
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-primary">
                        <tr>
                            <th style="width: 5%">No</th>
                            <th style="width: 15%">
                                <i class="bi bi-tag"></i> Kode Slot
                            </th>
                            <th style="width: 30%">
                                <i class="bi bi-journal-bookmark"></i> Nama Jurnal
                            </th>
                            <th style="width: 15%">
                                <i class="bi bi-building"></i> Penerbit
                            </th>
                            <th style="width: 10%" class="text-center">
                                <i class="bi bi-bar-chart"></i> Jumlah Slot
                            </th>
                            <th style="width: 10%" class="text-center">
                                <i class="bi bi-check2-circle"></i> Slot Terpakai
                            </th>
                            <th style="width: 10%" class="text-center">
                                <i class="bi bi-hourglass-split"></i> Sisa
                            </th>
                            <th style="width: 10%" class="text-center">Status</th>
                            <th style="width: 10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($results as $index => $slot)
                        <tr>
                            <td class="text-center">{{ $results->firstItem() + $index }}</td>
                            <td>
                                <strong class="text-primary">{{ $slot->kode_slot }}</strong>
                                @if($slot->journalMaster && $slot->journalMaster->accreditation)
                                    <br>
                                    <span class="badge bg-info mt-1">
                                        {{ $slot->journalMaster->accreditation }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $slot->journalMaster->nama_jurnal ?? '-' }}</strong>
                                @if($slot->journalMaster && $slot->journalMaster->bidang_ilmu)
                                    <br><small class="text-muted">{{ $slot->journalMaster->bidang_ilmu }}</small>
                                @endif
                            </td>
                            <td>{{ $slot->journalMaster->penerbit ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-primary fs-6">{{ $slot->jumlah_slot }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info fs-6">{{ $slot->slot_terpakai }}</span>
                            </td>
                            <td class="text-center">
                                @php
                                    $sisa = $slot->jumlah_slot - $slot->slot_terpakai;
                                    $badgeClass = $sisa > 0 ? 'success' : 'secondary';
                                @endphp
                                <span class="badge bg-{{ $badgeClass }} fs-6">{{ $sisa }}</span>
                            </td>
                            <td class="text-center">
                                @if($slot->slot_terpakai >= $slot->jumlah_slot)
                                    <span class="badge bg-danger">
                                        <i class="bi bi-x-circle"></i> Penuh
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Tersedia
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('public.loa.detail', $slot->id) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @include('partials.per-page-selector', ['paginator' => $results, 'default' => 15])
            @else
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> 
                Tidak ditemukan hasil untuk pencarian <strong>"{{ request('search_loa') }}"</strong>
                <br><br>
                <strong>Tips:</strong>
                <ul class="mb-0">
                    <li>Pastikan kode LOA yang Anda masukkan benar</li>
                    <li>Coba gunakan sebagian kode LOA (misal: "LOA-2024")</li>
                    <li>Hapus filter jurnal atau status jika digunakan</li>
                </ul>
            </div>
            @endif
        </div>
        @endif

        <!-- Login Link -->
        <div class="login-link">
            <a href="{{ route('login') }}">
                <i class="bi bi-box-arrow-in-right"></i> Login ke Sistem SIPERA
            </a>
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-4">
            <p class="text-white">
                <small>
                    &copy; {{ date('Y') }} SIPERA - Sistem Insentif dan Penghargaan Reviewer APJI
                    <br>
                    Jl. Watunganten I No.1, Karangrawa, Batursari, Kec. Mranggen, Kabupaten Demak, Jawa Tengah 59567
                </small>
            </p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
