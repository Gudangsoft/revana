<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Detail Slot Jurnal {{ $slot->kode_slot }} - {{ $slot->journalMaster->nama_jurnal ?? '' }}. Lihat artikel yang terdaftar dalam slot ini.">
    <meta name="keywords" content="detail slot jurnal, {{ $slot->kode_slot }}, artikel jurnal, publikasi ilmiah">
    <meta name="robots" content="index, follow">
    
    <title>Detail Slot {{ $slot->kode_slot }} - SIPERA</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        
        .detail-container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .detail-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .slot-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .info-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .article-card {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            background: white;
            transition: all 0.3s;
        }
        
        .article-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .back-button {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container detail-container">
        <!-- Back Button -->
        <div class="mb-3">
            <a href="{{ route('public.loa.search') }}" class="back-button">
                <i class="bi bi-arrow-left"></i> Kembali ke Pencarian
            </a>
        </div>

        <!-- Slot Header -->
        <div class="slot-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-3">
                        <i class="bi bi-calendar-range"></i> {{ $slot->kode_slot }}
                    </h2>
                    <h4 class="mb-2">{{ $slot->journalMaster->nama_jurnal ?? '-' }}</h4>
                    <p class="mb-0">
                        <i class="bi bi-building"></i> {{ $slot->journalMaster->penerbit ?? '-' }}
                        @if($slot->journalMaster && $slot->journalMaster->accreditation)
                            <span class="badge bg-light text-dark ms-2">
                                {{ $slot->journalMaster->accreditation }}
                            </span>
                        @endif
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="d-flex flex-column gap-2">
                        <div>
                            <span class="badge bg-light text-dark fs-5">
                                <i class="bi bi-bar-chart"></i> Jumlah: {{ $slot->jumlah_slot }}
                            </span>
                        </div>
                        <div>
                            <span class="badge bg-light text-dark fs-5">
                                <i class="bi bi-check-circle"></i> Terpakai: {{ $slot->slot_terpakai }}
                            </span>
                        </div>
                        <div>
                            @php
                                $sisa = $slot->jumlah_slot - $slot->slot_terpakai;
                            @endphp
                            <span class="badge {{ $sisa > 0 ? 'bg-success' : 'bg-secondary' }} fs-5">
                                <i class="bi bi-hourglass-split"></i> Sisa: {{ $sisa }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slot Information -->
        <div class="detail-card">
            <h4 class="mb-4">
                <i class="bi bi-info-circle"></i> Informasi Slot
            </h4>
            <div class="row">
                <div class="col-md-3">
                    <div class="info-card">
                        <small class="text-muted d-block">Volume</small>
                        <h5 class="mb-0">{{ $slot->volume ?? '-' }}</h5>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-card">
                        <small class="text-muted d-block">Nomor</small>
                        <h5 class="mb-0">{{ $slot->nomor ?? '-' }}</h5>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-card">
                        <small class="text-muted d-block">Bulan</small>
                        <h5 class="mb-0">{{ $slot->bulan ?? '-' }}</h5>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-card">
                        <small class="text-muted d-block">Tahun</small>
                        <h5 class="mb-0">{{ $slot->tahun ?? '-' }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Articles List -->
        <div class="detail-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <i class="bi bi-file-earmark-text"></i> Daftar Artikel dalam Slot Ini
                </h4>
                <span class="badge bg-primary fs-6">{{ $submissions->count() }} Artikel</span>
            </div>

            @forelse($submissions as $index => $submission)
            <div class="article-card">
                <div class="row">
                    <div class="col-md-1">
                        <div class="text-center">
                            <span class="badge bg-primary fs-5">{{ $index + 1 }}</span>
                        </div>
                    </div>
                    <div class="col-md-11">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="mb-2">
                                    <small class="text-muted">
                                        <i class="bi bi-tag"></i> Kode Artikel
                                    </small>
                                    <h6 class="mb-0">
                                        <code class="text-primary">{{ $submission->id_artikel ?? '-' }}</code>
                                    </h6>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">
                                        <i class="bi bi-person"></i> Nama Penulis
                                    </small>
                                    <h6 class="mb-0">{{ $submission->nama_penulis ?? '-' }}</h6>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="mb-2">
                                    <small class="text-muted">
                                        <i class="bi bi-journal-text"></i> Judul Artikel
                                    </small>
                                    <h6 class="mb-0">{{ $submission->judul_artikel ?? '-' }}</h6>
                                </div>
                                <div>
                                    <small class="text-muted">
                                        <i class="bi bi-link-45deg"></i> Link Publikasi
                                    </small>
                                    @if($submission->link_publikasi)
                                        <br>
                                        <a href="{{ $submission->link_publikasi }}" target="_blank" class="btn btn-sm btn-success mt-1">
                                            <i class="bi bi-box-arrow-up-right"></i> Buka Link
                                        </a>
                                    @else
                                        <h6 class="mb-0 text-muted">Belum tersedia</h6>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        @if($submission->status)
                        <div class="mt-2">
                            <span class="badge bg-info">
                                <i class="bi bi-info-circle"></i> Status: {{ $submission->status }}
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5">
                <i class="bi bi-inbox fs-1 text-muted"></i>
                <p class="mt-3 text-muted">Belum ada artikel yang terdaftar di slot ini</p>
            </div>
            @endforelse
        </div>

        <!-- Footer -->
        <div class="text-center mt-4">
            <p class="text-white">
                <small>
                    &copy; {{ date('Y') }} SIPERA - Sistem Insentif dan Penghargaan Reviewer APJI
                </small>
            </p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
