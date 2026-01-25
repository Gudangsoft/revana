@extends('pic.layouts.app')

@section('title', 'Monitoring Fasttrack')
@section('page-title', 'Monitoring Fasttrack')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
<style>
/* Sticky Table Styles */
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
    background-color: #fff8e1 !important;
}

.table-monitoring tbody tr:hover td.sticky-first,
.table-monitoring tbody tr:hover td.sticky-second {
    background-color: #fff8e1 !important;
}

.table-monitoring tbody tr:nth-child(even) td {
    background-color: #fffaf0;
}

.table-monitoring tbody tr:nth-child(even) td.sticky-first,
.table-monitoring tbody tr:nth-child(even) td.sticky-second {
    background-color: #fffaf0;
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
}

.quick-nav-btn:hover {
    background: #e9ecef;
}

.quick-nav-btn.active {
    background: #ffc107;
    color: #000;
    border-color: #ffc107;
}

.scroll-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    padding: 8px 12px;
    background: #fffaf0;
    border-radius: 6px;
    border: 1px solid #ffc107;
}

.scroll-nav-btn {
    padding: 6px 12px;
    border: 1px solid #ffc107;
    background: white;
    border-radius: 4px;
    cursor: pointer;
}

.scroll-nav-btn:hover {
    background: #ffc107;
    color: #000;
}
</style>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-1">Total Fasttrack</h6>
                        <h2 class="card-title mb-0">{{ $submissions->total() }}</h2>
                    </div>
                    <i class="bi bi-lightning-charge fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-1">Published</h6>
                        <h2 class="card-title mb-0">{{ $submissions->total() }}</h2>
                    </div>
                    <i class="bi bi-check-circle fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-1">Proses Langsung</h6>
                        <h2 class="card-title mb-0">100%</h2>
                    </div>
                    <i class="bi bi-speedometer2 fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <span><i class="bi bi-lightning-charge"></i> Monitoring Fasttrack (Proses Cepat)</span>
                <a href="{{ route('pic.fasttrack.create') }}" class="btn btn-dark btn-sm">
                    <i class="bi bi-plus-circle"></i> Input Fasttrack
                </a>
            </div>
            <div class="card-body">
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

                <!-- Info Box -->
                <div class="alert alert-warning mb-3">
                    <i class="bi bi-lightning-charge"></i> <strong>Fasttrack</strong> = Artikel sudah publish, langsung tercatat tanpa proses workflow.
                </div>

                <!-- Filter -->
                <form action="{{ route('pic.fasttrack.index') }}" method="GET" class="mb-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label for="tanggal_dari" class="form-label small mb-1">Tanggal Dari</label>
                            <input type="date" class="form-control form-control-sm" name="tanggal_dari" value="{{ request('tanggal_dari') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="tanggal_sampai" class="form-label small mb-1">Tanggal Sampai</label>
                            <input type="date" class="form-control form-control-sm" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="search" class="form-label small mb-1">Cari</label>
                            <input type="text" class="form-control form-control-sm" name="search" placeholder="Kode/Judul/Penulis..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-search"></i> Filter
                            </button>
                            <a href="{{ route('pic.fasttrack.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-clockwise"></i> Refresh
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Scroll Controls -->
                <div class="scroll-controls">
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="scroll-nav-btn" onclick="scrollTableTo('left')">
                            <i class="bi bi-chevron-double-left"></i> Kiri
                        </button>
                        <button type="button" class="scroll-nav-btn" onclick="scrollTableTo('right')">
                            Kanan <i class="bi bi-chevron-double-right"></i>
                        </button>
                    </div>
                    <div class="quick-nav">
                        <span class="me-2 small text-muted">Loncat ke:</span>
                        <button type="button" class="quick-nav-btn" data-target="submit">Submit</button>
                        <button type="button" class="quick-nav-btn" data-target="editor1">Editor 1</button>
                        <button type="button" class="quick-nav-btn" data-target="reviewer1">Reviewer 1</button>
                        <button type="button" class="quick-nav-btn" data-target="production">Production</button>
                    </div>
                </div>

                <!-- Table -->
                <div class="monitoring-scroll-wrapper" id="tableWrapper">
                    <table class="table table-monitoring table-bordered">
                        <thead>
                            <tr>
                                <th rowspan="2" class="sticky-first text-center">Kode Submit</th>
                                <th rowspan="2" class="sticky-second">ID Artikel</th>
                                <th rowspan="2">Judul</th>
                                <th rowspan="2">Link</th>
                                <th rowspan="2">Penulis</th>
                                <th rowspan="2">No HP</th>
                                <th colspan="4" class="text-center bg-info" data-section="submit">Author Access</th>
                                <th colspan="3" class="text-center bg-warning" data-section="editor1">Editor 1</th>
                                <th colspan="2" class="text-center bg-info">Author 1</th>
                                <th colspan="2" class="text-center bg-warning">Editor 2</th>
                                <th colspan="4" class="text-center bg-primary" data-section="reviewer1">Reviewer 1</th>
                                <th colspan="4" class="text-center bg-primary">Reviewer 2</th>
                                <th colspan="2" class="text-center bg-warning">Editor 3</th>
                                <th colspan="2" class="text-center bg-info">Author 2</th>
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
                                    <br><span class="badge bg-success mt-1"><i class="bi bi-check-circle-fill"></i></span>
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

                {{ $submissions->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.quick-nav-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const target = this.dataset.target;
        const wrapper = document.getElementById('tableWrapper');
        const th = document.querySelector(`th[data-section="${target}"]`);
        
        if (th && wrapper) {
            const offset = th.offsetLeft - 250;
            wrapper.scrollTo({ left: offset, behavior: 'smooth' });
            
            document.querySelectorAll('.quick-nav-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        }
    });
});

function scrollTableTo(direction) {
    const wrapper = document.getElementById('tableWrapper');
    const scrollAmount = wrapper.clientWidth * 0.8;
    
    if (direction === 'left') {
        wrapper.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    } else {
        wrapper.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    }
}
</script>
@endsection
