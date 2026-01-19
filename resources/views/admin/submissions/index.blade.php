@extends('layouts.app')

@section('title', 'Data Submit - ' . $appSettings['app_name'])
@section('page-title', 'Data Submit')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<style>
/* Sticky Table Styles */
.table-scroll-container {
    position: relative;
    overflow: hidden;
}

.table-scroll-wrapper {
    overflow-x: auto;
    overflow-y: visible;
    scrollbar-width: thin;
    scrollbar-color: #6c757d #dee2e6;
}

.table-scroll-wrapper::-webkit-scrollbar {
    height: 12px;
}

.table-scroll-wrapper::-webkit-scrollbar-track {
    background: #dee2e6;
    border-radius: 6px;
}

.table-scroll-wrapper::-webkit-scrollbar-thumb {
    background: #6c757d;
    border-radius: 6px;
}

.table-scroll-wrapper::-webkit-scrollbar-thumb:hover {
    background: #495057;
}

.table-sticky {
    border-collapse: separate;
    border-spacing: 0;
}

.table-sticky thead th {
    position: sticky;
    top: 0;
    background: #f8f9fa;
    z-index: 2;
    border-bottom: 2px solid #dee2e6;
}

/* Sticky first column (No) */
.table-sticky th.sticky-col-1,
.table-sticky td.sticky-col-1 {
    position: sticky;
    left: 0;
    background: #fff;
    z-index: 1;
    min-width: 40px;
    box-shadow: 2px 0 5px -2px rgba(0,0,0,0.1);
}

.table-sticky thead th.sticky-col-1 {
    z-index: 3;
    background: #f8f9fa;
}

/* Sticky second column (Kode Submit) */
.table-sticky th.sticky-col-2,
.table-sticky td.sticky-col-2 {
    position: sticky;
    left: 40px;
    background: #fff;
    z-index: 1;
    min-width: 130px;
    box-shadow: 2px 0 5px -2px rgba(0,0,0,0.1);
}

.table-sticky thead th.sticky-col-2 {
    z-index: 3;
    background: #f8f9fa;
}

/* Sticky last column (Aksi) */
.table-sticky th.sticky-col-last,
.table-sticky td.sticky-col-last {
    position: sticky;
    right: 0;
    background: #fff;
    z-index: 1;
    min-width: 150px;
    box-shadow: -2px 0 5px -2px rgba(0,0,0,0.1);
}

.table-sticky thead th.sticky-col-last {
    z-index: 3;
    background: #f8f9fa;
}

/* Hover effect for rows */
.table-sticky tbody tr:hover td {
    background-color: #f1f3f5 !important;
}

.table-sticky tbody tr:hover td.sticky-col-1,
.table-sticky tbody tr:hover td.sticky-col-2,
.table-sticky tbody tr:hover td.sticky-col-last {
    background-color: #f1f3f5 !important;
}

/* Scroll indicators */
.scroll-indicator {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(0,0,0,0.5);
    color: white;
    border: none;
    cursor: pointer;
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0.7;
    transition: opacity 0.3s;
}

.scroll-indicator:hover {
    opacity: 1;
}

.scroll-indicator.scroll-left {
    left: 180px;
}

.scroll-indicator.scroll-right {
    right: 160px;
}

.scroll-indicator.hidden {
    display: none;
}

/* Scroll progress bar */
.scroll-progress-container {
    background: #e9ecef;
    height: 4px;
    border-radius: 2px;
    margin-bottom: 10px;
    overflow: hidden;
}

.scroll-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #0d6efd, #0dcaf0);
    border-radius: 2px;
    transition: width 0.1s;
}

/* Column width hints */
.table-sticky th, .table-sticky td {
    white-space: nowrap;
    padding: 8px 12px;
}

.table-sticky td.wrap-text {
    white-space: normal;
    min-width: 200px;
    max-width: 300px;
}
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-file-earmark-text"></i> Data Submit</span>
                <div>
                    <a href="{{ route('admin.submissions.monitoring') }}" class="btn btn-info">
                        <i class="bi bi-bar-chart"></i> Monitoring
                    </a>
                    <div class="btn-group">
                        <a href="{{ route('admin.submissions.export', request()->query()) }}" class="btn btn-success">
                            <i class="bi bi-download"></i> Export
                        </a>
                        <button type="button" class="btn btn-success dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('admin.submissions.export', request()->query()) }}"><i class="bi bi-file-earmark-excel"></i> Export Data</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#importModal"><i class="bi bi-upload"></i> Import Data</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.submissions.template') }}"><i class="bi bi-file-earmark-arrow-down"></i> Download Template</a></li>
                        </ul>
                    </div>
                    <a href="{{ route('admin.submissions.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif
                
                @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle"></i> {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif
                
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <!-- Filter -->
                <form action="{{ route('admin.submissions.index') }}" method="GET" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label for="tanggal_dari" class="form-label">Tanggal Dari</label>
                            <input type="date" class="form-control" id="tanggal_dari" name="tanggal_dari" value="{{ request('tanggal_dari') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="tanggal_sampai" class="form-label">Tanggal Sampai</label>
                            <input type="date" class="form-control" id="tanggal_sampai" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="journal_master_id" class="form-label">Jurnal</label>
                            <select class="form-select" id="journal_master_id" name="journal_master_id">
                                <option value="">-- Semua Jurnal --</option>
                                @foreach($journals as $journal)
                                    <option value="{{ $journal->id }}" {{ request('journal_master_id') == $journal->id ? 'selected' : '' }}>
                                        {{ $journal->nama_jurnal }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">-- Semua Status --</option>
                                @foreach($statusOptions as $key => $value)
                                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.submissions.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Scroll Progress Indicator -->
                <div class="scroll-progress-container">
                    <div class="scroll-progress-bar" id="scrollProgress" style="width: 0%"></div>
                </div>
                
                <!-- Scroll Hint -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted">
                        <i class="bi bi-arrows-expand"></i> Geser tabel ke kanan/kiri untuk melihat semua kolom
                    </small>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary" id="scrollLeftBtn" title="Scroll Kiri">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="scrollRightBtn" title="Scroll Kanan">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <div class="table-scroll-container">
                    <div class="table-scroll-wrapper" id="tableScrollWrapper">
                        <table class="table table-hover table-sm table-sticky">
                            <thead>
                                <tr>
                                    <th class="sticky-col-1">#</th>
                                    <th class="sticky-col-2">Kode Submit</th>
                                    <th>ID Artikel</th>
                                    <th>Judul Artikel</th>
                                    <th>Nama Penulis</th>
                                    <th>No HP</th>
                                    <th>Username</th>
                                    <th>Password</th>
                                    <th>PIC Marketing</th>
                                    <th>Petugas Submit</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th class="sticky-col-last text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($submissions as $submission)
                                <tr>
                                    <td class="sticky-col-1">{{ $loop->iteration + ($submissions->currentPage() - 1) * $submissions->perPage() }}</td>
                                    <td class="sticky-col-2"><code class="text-primary">{{ $submission->kode_submit }}</code></td>
                                    <td>{{ $submission->id_artikel }}</td>
                                    <td class="wrap-text">
                                        <span title="{{ $submission->judul_artikel }}">{{ Str::limit($submission->judul_artikel, 40) }}</span>
                                        @if($submission->link_artikel)
                                            <a href="{{ $submission->link_artikel }}" target="_blank" class="text-primary ms-1">
                                                <i class="bi bi-link-45deg"></i>
                                            </a>
                                        @endif
                                    </td>
                                    <td>{{ $submission->nama_penulis }}</td>
                                    <td>{{ $submission->no_hp_penulis ?? '-' }}</td>
                                    <td><code class="text-success">{{ $submission->username_author ?? '-' }}</code></td>
                                    <td><code class="text-warning">{{ $submission->password_author ?? '-' }}</code></td>
                                    <td>{{ $submission->pic_marketing ?? '-' }}</td>
                                    <td>{{ $submission->petugasSubmit?->name ?? '-' }}</td>
                                    <td>{{ $submission->tanggal_submit?->format('d/m/Y') }}</td>
                                    <td><span class="badge {{ $submission->status_badge_class }}">{{ $submission->status_label }}</span></td>
                                    <td class="sticky-col-last">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.submissions.show', $submission) }}" class="btn btn-info" title="Lihat">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.submissions.process', $submission) }}" class="btn btn-primary" title="Proses">
                                                <i class="bi bi-gear"></i>
                                            </a>
                                            <a href="{{ route('admin.submissions.history', $submission) }}" class="btn btn-secondary" title="Histori">
                                                <i class="bi bi-clock-history"></i>
                                            </a>
                                            <a href="{{ route('admin.submissions.edit', $submission) }}" class="btn btn-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.submissions.destroy', $submission) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus submission ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="13" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        Belum ada data submission
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-3">
                    @include('components.simple-pagination', ['paginator' => $submissions->withQueryString()])
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const wrapper = document.getElementById('tableScrollWrapper');
    const progressBar = document.getElementById('scrollProgress');
    const scrollLeftBtn = document.getElementById('scrollLeftBtn');
    const scrollRightBtn = document.getElementById('scrollRightBtn');
    
    // Update progress bar on scroll
    function updateScrollProgress() {
        const scrollLeft = wrapper.scrollLeft;
        const scrollWidth = wrapper.scrollWidth - wrapper.clientWidth;
        const progress = scrollWidth > 0 ? (scrollLeft / scrollWidth) * 100 : 0;
        progressBar.style.width = progress + '%';
        
        // Update button states
        scrollLeftBtn.disabled = scrollLeft <= 0;
        scrollRightBtn.disabled = scrollLeft >= scrollWidth;
    }
    
    wrapper.addEventListener('scroll', updateScrollProgress);
    
    // Scroll buttons
    const scrollAmount = 300;
    
    scrollLeftBtn.addEventListener('click', function() {
        wrapper.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    });
    
    scrollRightBtn.addEventListener('click', function() {
        wrapper.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    });
    
    // Keyboard navigation
    wrapper.setAttribute('tabindex', '0');
    wrapper.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') {
            wrapper.scrollBy({ left: -100, behavior: 'smooth' });
        } else if (e.key === 'ArrowRight') {
            wrapper.scrollBy({ left: 100, behavior: 'smooth' });
        }
    });
    
    // Initial state
    updateScrollProgress();
});
</script>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">
                    <i class="bi bi-upload"></i> Import Data Submissions
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.submissions.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        <strong>Petunjuk Import:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Download template terlebih dahulu</li>
                            <li>Isi data sesuai format template</li>
                            <li>Kolom wajib: id_artikel, judul_artikel, nama_penulis</li>
                            <li>Format file: Excel (.xlsx, .xls) atau CSV</li>
                            <li>Maksimal ukuran file: 10MB</li>
                        </ul>
                    </div>
                    
                    <div class="mb-3">
                        <label for="importFile" class="form-label">Pilih File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="importFile" name="file" 
                               accept=".xlsx,.xls,.csv" required>
                    </div>
                    
                    <div class="text-center">
                        <a href="{{ route('admin.submissions.template') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-file-earmark-arrow-down"></i> Download Template
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-upload"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
