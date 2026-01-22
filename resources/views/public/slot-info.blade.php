<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Informasi Slot Jurnal dan Artikel Publikasi SIPERA APJI. Lihat daftar artikel yang telah dipublikasikan beserta informasi penulis dan link publikasi.">
    <meta name="keywords" content="info slot jurnal, daftar artikel, publikasi ilmiah, SIPERA, APJI, artikel jurnal, slot jurnal Indonesia">
    <meta name="author" content="APJI - Asosiasi Penerbit Jurnal Indonesia">
    <meta name="robots" content="index, follow">
    <meta name="language" content="Indonesian">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="Informasi Slot Jurnal - SIPERA APJI">
    <meta property="og:description" content="Lihat daftar artikel yang telah dipublikasikan di slot jurnal SIPERA APJI">
    <meta property="og:site_name" content="SIPERA - APJI">
    <meta property="og:url" content="{{ url()->current() }}">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Informasi Slot Jurnal - SIPERA APJI">
    <meta name="twitter:description" content="Lihat daftar artikel yang telah dipublikasikan di slot jurnal SIPERA APJI">
    
    <title>Informasi Slot Jurnal - SIPERA</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        
        .info-container {
            max-width: 1400px;
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
        
        .filter-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .article-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }
        
        .article-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        
        .article-number {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .info-label {
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 1rem;
            color: #212529;
            margin-bottom: 15px;
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
    <div class="container info-container">
        <div class="header-card">
            <div class="logo-section">
                <h1><i class="bi bi-journal-bookmark-fill"></i> SIPERA</h1>
                <p class="lead">Sistem Insentif dan Penghargaan Reviewer APJI</p>
                <p class="text-muted">Informasi Slot Jurnal dan Artikel Publikasi</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <i class="bi bi-file-earmark-text text-primary"></i>
                    <h3 class="text-primary">{{ $stats['total_submissions'] }}</h3>
                    <p>Total Artikel</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <i class="bi bi-calendar-range text-success"></i>
                    <h3 class="text-success">{{ $stats['total_slots'] }}</h3>
                    <p>Total Slot Jurnal</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <i class="bi bi-journal-bookmark text-info"></i>
                    <h3 class="text-info">{{ $stats['total_journals'] }}</h3>
                    <p>Jurnal Aktif</p>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-card">
            <h5 class="mb-3"><i class="bi bi-funnel"></i> Filter Artikel</h5>
            <form method="GET" action="{{ route('public.slot.info') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Jurnal</label>
                        <select name="journal_id" class="form-select">
                            <option value="">-- Semua Jurnal --</option>
                            @foreach($journals as $journal)
                                <option value="{{ $journal->id }}" {{ request('journal_id') == $journal->id ? 'selected' : '' }}>
                                    {{ $journal->nama_jurnal }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Slot</label>
                        <select name="slot_id" class="form-select">
                            <option value="">-- Semua Slot --</option>
                            @foreach($slots as $slot)
                                <option value="{{ $slot->id }}" {{ request('slot_id') == $slot->id ? 'selected' : '' }}>
                                    {{ $slot->kode_slot }} - {{ $slot->journalMaster->nama_jurnal ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Urutkan</label>
                        <div class="input-group">
                            <select name="sort_by" class="form-select">
                                <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Tanggal</option>
                                <option value="id_artikel" {{ request('sort_by') == 'id_artikel' ? 'selected' : '' }}>Kode Artikel</option>
                                <option value="nama_penulis" {{ request('sort_by') == 'nama_penulis' ? 'selected' : '' }}>Penulis</option>
                            </select>
                            <select name="sort_order" class="form-select" style="max-width: 80px;">
                                <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>▼</option>
                                <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>▲</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel"></i> Terapkan Filter
                    </button>
                    <a href="{{ route('public.slot.info') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Articles List -->
        <div class="mb-3">
            <h4 class="text-white">
                <i class="bi bi-list-ul"></i> Daftar Artikel
                <span class="badge bg-light text-dark ms-2">{{ $submissions->total() }} Artikel</span>
            </h4>
        </div>

        @forelse($submissions as $index => $submission)
        <div class="article-card">
            <div class="row align-items-center">
                <div class="col-md-1 text-center mb-3 mb-md-0">
                    <div class="article-number">{{ $submissions->firstItem() + $index }}</div>
                </div>
                <div class="col-md-11">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="info-label">
                                <i class="bi bi-tag-fill"></i> Kode Artikel
                            </div>
                            <div class="info-value">
                                <code class="fs-5 text-primary">{{ $submission->id_artikel ?? '-' }}</code>
                            </div>
                            
                            <div class="info-label">
                                <i class="bi bi-person-fill"></i> Nama Penulis
                            </div>
                            <div class="info-value">
                                <strong>{{ $submission->nama_penulis ?? '-' }}</strong>
                            </div>
                            
                            <div class="info-label">
                                <i class="bi bi-calendar-check"></i> Slot Jurnal
                            </div>
                            <div class="info-value">
                                @if($submission->journalSlot)
                                    <span class="badge bg-primary">{{ $submission->journalSlot->kode_slot }}</span>
                                    <small class="text-muted d-block mt-1">
                                        Vol. {{ $submission->journalSlot->volume }}, 
                                        No. {{ $submission->journalSlot->nomor }}, 
                                        {{ $submission->journalSlot->bulan }} {{ $submission->journalSlot->tahun }}
                                    </small>
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="info-label">
                                <i class="bi bi-journal-text"></i> Judul Artikel
                            </div>
                            <div class="info-value">
                                {{ $submission->judul_artikel ?? '-' }}
                            </div>
                            
                            <div class="info-label">
                                <i class="bi bi-journal-bookmark"></i> Nama Jurnal
                            </div>
                            <div class="info-value">
                                @if($submission->journalSlot && $submission->journalSlot->journalMaster)
                                    <strong>{{ $submission->journalSlot->journalMaster->nama_jurnal }}</strong>
                                    @if($submission->journalSlot->journalMaster->accreditation)
                                        <span class="badge bg-info ms-1">
                                            {{ $submission->journalSlot->journalMaster->accreditation }}
                                        </span>
                                    @endif
                                @else
                                    -
                                @endif
                            </div>
                            
                            <div class="info-label">
                                <i class="bi bi-link-45deg"></i> Link Publikasi
                            </div>
                            <div class="info-value">
                                @if($submission->link_publikasi)
                                    <a href="{{ $submission->link_publikasi }}" target="_blank" class="btn btn-success btn-sm">
                                        <i class="bi bi-box-arrow-up-right"></i> Buka Link
                                    </a>
                                @else
                                    <span class="text-muted">Belum tersedia</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    @if($submission->status)
                    <div class="mt-2">
                        <span class="badge bg-secondary">
                            <i class="bi bi-info-circle"></i> Status: {{ $submission->status }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="article-card text-center py-5">
            <i class="bi bi-inbox fs-1 text-muted"></i>
            <p class="mt-3 text-muted">Belum ada artikel yang terdaftar</p>
        </div>
        @endforelse

        <!-- Pagination -->
        @if($submissions->hasPages())
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="text-white">
                Menampilkan {{ $submissions->firstItem() ?? 0 }} - {{ $submissions->lastItem() ?? 0 }} dari {{ $submissions->total() }} artikel
            </div>
            <div>
                {{ $submissions->links() }}
            </div>
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
