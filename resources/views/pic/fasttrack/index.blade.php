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
