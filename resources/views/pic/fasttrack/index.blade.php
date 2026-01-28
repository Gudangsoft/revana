@extends('pic.layouts.app')

@section('title', 'Data Submit Fasttrack')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center bg-warning">
                <span><i class="bi bi-lightning-charge text-dark"></i> <strong>Data Submit Fasttrack</strong></span>
                <a href="{{ route('pic.fasttrack.create') }}" class="btn btn-dark btn-sm">
                    <i class="bi bi-plus-circle"></i> Input Fasttrack
                </a>
            </div>
            <div class="card-body">
                <!-- Summary Cards -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark">
                            <div class="card-body text-center p-3">
                                <h6 class="card-title mb-1 small">Total Fasttrack</h6>
                                <h3 class="mb-0">{{ $submissions->total() }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center p-3">
                                <h6 class="card-title mb-1 small">Published</h6>
                                <h3 class="mb-0">{{ $submissions->where('status', 'PUBLISHED')->count() }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center p-3">
                                <h6 class="card-title mb-1 small">Sedang Proses</h6>
                                <h3 class="mb-0">{{ $submissions->where('status', '!=', 'PUBLISHED')->count() }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center p-3">
                                <h6 class="card-title mb-1 small">Total Data</h6>
                                <h3 class="mb-0">{{ $submissions->count() }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Form -->
                <form action="{{ route('pic.fasttrack.index') }}" method="GET" class="mb-3">
                    <div class="row g-2">
                        <div class="col-md-2">
                            <label class="form-label small">Tanggal Dari</label>
                            <input type="date" name="tanggal_dari" class="form-control form-control-sm" value="{{ request('tanggal_dari') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Tanggal Sampai</label>
                            <input type="date" name="tanggal_sampai" class="form-control form-control-sm" value="{{ request('tanggal_sampai') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Cari</label>
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Kode/Judul/Penulis..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-search"></i> Filter
                                </button>
    line-height: 1;
    vertical-align: middle;
    height: 32px;
}

.table-monitoring thead tr:nth-child(2) th {
    position: sticky;
    top: 32px;
    z-index: 20;
    line-height: 1;
    vertical-align: middle;
    height: 32px;
}

.table-monitoring thead th.bg-info {
    background-color: #0dcaf0 !important;
    color: #000 !important;
    z-index: 20 !important;
    position: sticky !important;
}

.table-monitoring thead th.bg-warning {
    background-color: #ffc107 !important;
    color: #000 !important;
    z-index: 20 !important;
    position: sticky !important;
}

.table-monitoring thead th.bg-primary {
    background-color: #0d6efd !important;
    color: #fff !important;
    z-index: 20 !important;
    position: sticky !important;
}

.table-monitoring thead th.bg-success {
    background-color: #198754 !important;
    color: #fff !important;
    z-index: 20 !important;
    position: sticky !important;
}

.table-monitoring thead th.bg-dark {
    background-color: #212529 !important;
    color: #fff !important;
    z-index: 20 !important;
    position: sticky !important;
}

/* Sticky first column */
.table-monitoring th.sticky-first,
.table-monitoring td.sticky-first {
    position: sticky;
    left: 0;
    z-index: 3;
    background: #fff;
    min-width: 110px;
    box-shadow: 2px 0 4px -2px rgba(0,0,0,0.1);
}

.table-monitoring thead th.sticky-first {
    z-index: 21;
    background-color: #212529 !important;
    color: #fff !important;
}

/* Sticky second column */
.table-monitoring th.sticky-second,
.table-monitoring td.sticky-second {
    position: sticky;
    left: 110px;
    z-index: 3;
    background: #fff;
    min-width: 90px;
    box-shadow: 2px 0 4px -2px rgba(0,0,0,0.1);
}

.table-monitoring thead th.sticky-second {
    z-index: 21;
    background-color: #212529 !important;
    color: #fff !important;
}

.table-monitoring tbody td {
    padding: 4px;
    border: 1px solid #dee2e6;
    white-space: nowrap;
    line-height: 1;
    vertical-align: middle;
    height: 30px;
}

.table-monitoring tbody tr:hover td {
    background-color: #f1f3f5;
}

.table-monitoring tbody tr:hover td.sticky-first,
.table-monitoring tbody tr:hover td.sticky-second {
    background-color: #e9ecef;
}

.table-monitoring tbody tr:nth-child(even) td {
    background-color: #f9fafb;
}

.table-monitoring tbody tr:nth-child(even) td.sticky-first,
.table-monitoring tbody tr:nth-child(even) td.sticky-second {
    background-color: #f9fafb;
}

/* Style untuk petugas belum ditugaskan */
.petugas-kosong {
    color: #dc3545;
    font-style: italic;
    font-size: 0.75rem;
}

.petugas-kosong i {
    margin-right: 2px;
}

/* Scroll controls */
.scroll-controls {
    margin-bottom: 10px;
    padding: 8px 12px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 6px;
    border: 1px solid #dee2e6;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    position: relative;
    z-index: 20;
}

.scroll-nav-btn {
    background: linear-gradient(135deg, #0d6efd, #0b5ed7);
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.875rem;
    transition: all 0.2s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    position: relative;
    z-index: 21;
}

.scroll-nav-btn:hover:not(:disabled) {
    background: linear-gradient(135deg, #0b5ed7, #0a58ca);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    transform: translateY(-1px);
}

.scroll-nav-btn:disabled {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    cursor: not-allowed;
    opacity: 0.5;
}

.scroll-position-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    max-width: 300px;
}

.scroll-position-bar {
    width: 200px;
    height: 8px;
    background: #dee2e6;
    border-radius: 4px;
    overflow: hidden;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
}

.scroll-position-fill {
    height: 100%;
    background: linear-gradient(90deg, #ffc107, #fd7e14);
    transition: width 0.2s ease;
}

.quick-nav {
    margin-top: 10px;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
}

.quick-nav-btn {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
    border: none;
    padding: 5px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.75rem;
    transition: all 0.2s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    position: relative;
    z-index: 21;
}

.quick-nav-btn:hover {
    background: linear-gradient(135deg, #5a6268, #495057);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.quick-nav-btn.active {
    background: linear-gradient(135deg, #ffc107, #fd7e14);
    color: #000;
    box-shadow: 0 4px 8px rgba(255, 193, 7, 0.3);
}

/* Summary cards */
.summary-cards {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
}

.summary-card {
    flex: 0 0 auto;
    min-width: 120px;
    max-width: 180px;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    padding: 12px 15px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.summary-card h6 {
    font-size: 0.7rem;
    color: #6c757d;
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.summary-card .value {
    font-size: 1.75rem;
    font-weight: bold;
    color: #212529;
}

.summary-card.fasttrack {
    border-left: 4px solid #ffc107;
}

.summary-card.published {
    border-left: 4px solid #198754;
}
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-lightning-charge text-warning"></i> Data Submit Fasttrack</span>
                <a href="{{ route('pic.fasttrack.create') }}" class="btn btn-warning btn-sm">
                    <i class="bi bi-plus-circle"></i> Input Fasttrack
                </a>
            </div>
            <div class="card-body">
                <!-- Summary Cards -->
                <div class="summary-cards mb-3">
                    <div class="summary-card fasttrack">
                        <h6>Total Fasttrack</h6>
                        <div class="value">{{ $submissions->total() }}</div>
                    </div>
                    <div class="summary-card published">
                        <h6>Published</h6>
                        <div class="value">{{ $submissions->total() }}</div>
                    </div>
                </div>

                <!-- Filter Form -->
                <form action="{{ route('pic.fasttrack.index') }}" method="GET" class="mb-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-auto">
                            <label class="form-label small mb-1">Tanggal Dari</label>
                            <input type="date" name="tanggal_dari" class="form-control form-control-sm" style="width: 150px;" value="{{ request('tanggal_dari') }}">
                        </div>
                        <div class="col-auto">
                            <label class="form-label small mb-1">Tanggal Sampai</label>
                            <input type="date" name="tanggal_sampai" class="form-control form-control-sm" style="width: 150px;" value="{{ request('tanggal_sampai') }}">
                        </div>
                        <div class="col-auto">
                            <label class="form-label small mb-1">Cari</label>
                            <input type="text" name="search" class="form-control form-control-sm" style="width: 200px;" placeholder="Kode/Judul/Penulis..." value="{{ request('search') }}">
                        </div>
                        <div class="col-auto">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Filter
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-search"></i> Filter
                                </button>
                                <a href="{{ route('pic.fasttrack.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-x-circle"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Data Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Kode Submit</th>
                                <th>ID Artikel</th>
                                <th>Judul</th>
                                <th>Jurnal</th>
                                <th>Penulis</th>
                                <th>Marketing</th>
                                <th>Petugas Submit</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th style="width: 150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($submissions as $s)
                            <tr>
                                <td>{{ $loop->iteration + ($submissions->currentPage() - 1) * $submissions->perPage() }}</td>
                                <td>
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-lightning-charge"></i> {{ $s->kode_submit }}
                                    </span>
                                </td>
                                <td>{{ $s->id_artikel ?? '-' }}</td>
                                <td>{{ Str::limit($s->judul_artikel, 50) }}</td>
                                <td><small>{{ $s->journalSlot->journalMaster->name ?? '-' }}</small></td>
                                <td>{{ Str::limit($s->nama_penulis, 30) }}</td>
                                <td>{{ $s->marketing->name ?? '-' }}</td>
                                <td>{{ $s->petugasSubmit->name ?? '-' }}</td>
                                <td>
                                    @if($s->status == 'PUBLISHED' || $s->production_valid)
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Published</span>
                                    @elseif($s->link_publish)
                                        <span class="badge bg-info"><i class="bi bi-hourglass-half"></i> Proses</span>
                                    @else
                                        <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle"></i> Perlu Penugasan</span>
                                    @endif
                                </td>
                                <td><small>{{ $s->tanggal_submit ? date('d/m/Y', strtotime($s->tanggal_submit)) : ($s->created_at ? $s->created_at->format('d/m/Y') : '-') }}</small></td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('pic.fasttrack.show', $s) }}" class="btn btn-info btn-sm" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('pic.fasttrack.edit', $s) }}" class="btn btn-warning btn-sm" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                    <p class="mb-0 mt-2">Belum ada data fasttrack submission</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($submissions->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small">
                        Menampilkan {{ $submissions->firstItem() }} - {{ $submissions->lastItem() }} dari {{ $submissions->total() }} data
                    </div>
                    {{ $submissions->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
                                    @else
                                        <span class="petugas-kosong"><i class="bi bi-exclamation-circle"></i> Belum ditugaskan</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($s->petugas_author1_id == $picId)
                                    <i class="bi {{ $s->author1_valid ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }} valid-toggle" 
                                       style="cursor: pointer;" 
                                       onclick="toggleValid(this, {{ $s->id }}, 'author1_valid', {{ $s->author1_valid ? 'true' : 'false' }})"
                                       title="Klik untuk toggle valid"></i>
                                    @else
                                    <i class="bi {{ $s->author1_valid ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }}" 
                                       title="Hanya petugas yang ditugaskan yang bisa validasi"></i>
                                    @endif
                                </td>
                                
                                <!-- Editor 2 -->
                                <td>
                                    @if($s->petugasEditor2)
                                        {{ $s->petugasEditor2->name }}
                                    @else
                                        <span class="petugas-kosong"><i class="bi bi-exclamation-circle"></i> Belum ditugaskan</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($s->petugas_editor2_id == $picId)
                                    <i class="bi {{ $s->editor2_valid ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }} valid-toggle" 
                                       style="cursor: pointer;" 
                                       onclick="toggleValid(this, {{ $s->id }}, 'editor2_valid', {{ $s->editor2_valid ? 'true' : 'false' }})"
                                       title="Klik untuk toggle valid"></i>
                                    @else
                                    <i class="bi {{ $s->editor2_valid ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }}" 
                                       title="Hanya petugas yang ditugaskan yang bisa validasi"></i>
                                    @endif
                                </td>
                                
                                <!-- Reviewer 1 -->
                                <td>
                                    @if($s->petugasReviewer1)
                                        {{ $s->petugasReviewer1->name }}
                                    @else
                                        <span class="petugas-kosong"><i class="bi bi-exclamation-circle"></i> Belum ditugaskan</span>
                                    @endif
                                </td>
                                <td>@if($s->username_reviewer1)<code>{{ $s->username_reviewer1 }}/{{ $s->password_reviewer1 ?? '-' }}</code>@else - @endif</td>
                                <td title="{{ $s->catatan_reviewer1 }}">{{ Str::limit($s->catatan_reviewer1, 10) ?? '-' }}</td>
                                <td class="text-center">
                                    @if($s->petugas_reviewer1_id == $picId)
                                    <i class="bi {{ $s->reviewer1_valid ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }} valid-toggle" 
                                       style="cursor: pointer;" 
                                       onclick="toggleValid(this, {{ $s->id }}, 'reviewer1_valid', {{ $s->reviewer1_valid ? 'true' : 'false' }})"
                                       title="Klik untuk toggle valid"></i>
                                    @else
                                    <i class="bi {{ $s->reviewer1_valid ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }}" 
                                       title="Hanya petugas yang ditugaskan yang bisa validasi"></i>
                                    @endif
                                </td>
                                
                                <!-- Reviewer 2 -->
                                <td>
                                    @if($s->petugasReviewer2)
                                        {{ $s->petugasReviewer2->name }}
                                    @else
                                        <span class="petugas-kosong"><i class="bi bi-exclamation-circle"></i> Belum ditugaskan</span>
                                    @endif
                                </td>
                                <td>@if($s->username_reviewer2)<code>{{ $s->username_reviewer2 }}/{{ $s->password_reviewer2 ?? '-' }}</code>@else - @endif</td>
                                <td title="{{ $s->catatan_reviewer2 }}">{{ Str::limit($s->catatan_reviewer2, 10) ?? '-' }}</td>
                                <td class="text-center">
                                    @if($s->petugas_reviewer2_id == $picId)
                                    <i class="bi {{ $s->reviewer2_valid ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }} valid-toggle" 
                                       style="cursor: pointer;" 
                                       onclick="toggleValid(this, {{ $s->id }}, 'reviewer2_valid', {{ $s->reviewer2_valid ? 'true' : 'false' }})"
                                       title="Klik untuk toggle valid"></i>
                                    @else
                                    <i class="bi {{ $s->reviewer2_valid ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }}" 
                                       title="Hanya petugas yang ditugaskan yang bisa validasi"></i>
                                    @endif
                                </td>
                                
                                <!-- Editor 3 -->
                                <td>
                                    @if($s->petugasEditor3)
                                        {{ $s->petugasEditor3->name }}
                                    @else
                                        <span class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-dash-circle"></i> Opsional</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($s->petugas_editor3_id == $picId)
                                    <i class="bi {{ $s->editor3_valid ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }} valid-toggle" 
                                       style="cursor: pointer;" 
                                       onclick="toggleValid(this, {{ $s->id }}, 'editor3_valid', {{ $s->editor3_valid ? 'true' : 'false' }})"
                                       title="Klik untuk toggle valid"></i>
                                    @else
                                    <i class="bi {{ $s->editor3_valid ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }}" 
                                       title="Hanya petugas yang ditugaskan yang bisa validasi"></i>
                                    @endif
                                </td>
                                
                                <!-- Author 2 -->
                                <td>
                                    @if($s->petugasAuthor2)
                                        {{ $s->petugasAuthor2->name }}
                                    @else
                                        <span class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-dash-circle"></i> Opsional</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($s->petugas_author2_id == $picId)
                                    <i class="bi {{ $s->author2_valid ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }} valid-toggle" 
                                       style="cursor: pointer;" 
                                       onclick="toggleValid(this, {{ $s->id }}, 'author2_valid', {{ $s->author2_valid ? 'true' : 'false' }})"
                                       title="Klik untuk toggle valid"></i>
                                    @else
                                    <i class="bi {{ $s->author2_valid ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }}" 
                                       title="Hanya petugas yang ditugaskan yang bisa validasi"></i>
                                    @endif
                                </td>
                                
                                <!-- Production -->
                                <td>
                                    @if(!$s->link_publish)
                                        <span class="petugas-kosong"><i class="bi bi-exclamation-circle"></i> Belum ditugaskan</span>
                                    @elseif($s->petugasProduction)
                                        {{ $s->petugasProduction->name }}
                                    @elseif($s->petugasSubmit)
                                        {{ $s->petugasSubmit->name }}
                                    @elseif($s->marketing)
                                        {{ $s->marketing->name }}
                                    @else
                                        <span class="petugas-kosong"><i class="bi bi-exclamation-circle"></i> Belum ditugaskan</span>
                                    @endif
                                </td>
                                <td>
                                    @if($s->link_publish)
                                        <a href="{{ $s->link_publish }}" target="_blank" class="btn btn-sm btn-success" style="padding: 2px 6px; font-size: 0.7rem;">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($s->petugas_production_id == $picId)
                                    <i class="bi {{ $s->production_valid ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }} valid-toggle" 
                                       style="cursor: pointer;" 
                                       onclick="toggleValid(this, {{ $s->id }}, 'production_valid', {{ $s->production_valid ? 'true' : 'false' }})"
                                       title="Klik untuk toggle valid"></i>
                                    @else
                                    <i class="bi {{ $s->production_valid ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }}" 
                                       title="Hanya petugas yang ditugaskan yang bisa validasi"></i>
                                    @endif
                                </td>
                                
                                <td class="text-center">
                                    <a href="{{ route('pic.fasttrack.show', $s) }}" class="btn btn-info btn-sm" style="padding: 2px 6px; font-size: 0.7rem;">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="32" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1"></i>
                                    <p class="mb-0 mt-2">Belum ada data fasttrack</p>
                                    <a href="{{ route('pic.fasttrack.create') }}" class="btn btn-warning btn-sm mt-2">
                                        <i class="bi bi-plus-circle"></i> Input Fasttrack
                                    </a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $submissions->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
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

// Toggle Valid Function
function toggleValid(icon, submissionId, field, currentValue) {
    const stage = field.replace('_valid', '');
    
    icon.style.opacity = '0.5';
    
    fetch('/pic/submissions/toggle-valid', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            submission_id: submissionId,
            stage: stage
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        icon.style.opacity = '1';
        if (data.success) {
            const isValid = data.is_valid;
            if (isValid) {
                icon.classList.remove('bi-circle', 'text-muted');
                icon.classList.add('bi-check-circle-fill', 'text-success');
            } else {
                icon.classList.remove('bi-check-circle-fill', 'text-success');
                icon.classList.add('bi-circle', 'text-muted');
            }
            icon.setAttribute('onclick', `toggleValid(this, ${submissionId}, '${field}', ${isValid})`);
        } else {
            alert('Gagal: ' + (data.message || 'Error'));
        }
    })
    .catch(error => {
        icon.style.opacity = '1';
        console.error('Error:', error);
        alert('Error: ' + error.message);
    });
}
</script>
@endsection
