@extends('marketing.layouts.app')

@section('title', 'Monitoring Artikel')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi bi-file-earmark-text"></i> Artikel Saya
    </h4>
    <div>
        <a href="{{ route('marketing.submissions.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Submit Artikel Baru
        </a>
        <span class="badge bg-secondary fs-6 ms-2">Total: {{ $submissions->total() }} artikel</span>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Filter -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" 
                       placeholder="Cari kode/judul/penulis..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="SUBMITTED" {{ request('status') == 'SUBMITTED' ? 'selected' : '' }}>Submitted</option>
                    <option value="EDITOR1" {{ request('status') == 'EDITOR1' ? 'selected' : '' }}>Editor 1</option>
                    <option value="AUTHOR1" {{ request('status') == 'AUTHOR1' ? 'selected' : '' }}>Author 1</option>
                    <option value="EDITOR2" {{ request('status') == 'EDITOR2' ? 'selected' : '' }}>Editor 2</option>
                    <option value="REVIEWER1" {{ request('status') == 'REVIEWER1' ? 'selected' : '' }}>Reviewer 1</option>
                    <option value="REVIEWER2" {{ request('status') == 'REVIEWER2' ? 'selected' : '' }}>Reviewer 2</option>
                    <option value="EDITOR3" {{ request('status') == 'EDITOR3' ? 'selected' : '' }}>Editor 3</option>
                    <option value="AUTHOR2" {{ request('status') == 'AUTHOR2' ? 'selected' : '' }}>Author 2</option>
                    <option value="PRODUCTION" {{ request('status') == 'PRODUCTION' ? 'selected' : '' }}>Production</option>
                    <option value="PUBLISHED" {{ request('status') == 'PUBLISHED' ? 'selected' : '' }}>Published</option>
                    <option value="REJECTED" {{ request('status') == 'REJECTED' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="start_date" class="form-control form-control-sm" 
                       placeholder="Dari Tanggal" value="{{ request('start_date') }}" 
                       title="Dari Tanggal">
            </div>
            <div class="col-md-2">
                <input type="date" name="end_date" class="form-control form-control-sm" 
                       placeholder="Sampai Tanggal" value="{{ request('end_date') }}" 
                       title="Sampai Tanggal">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="{{ route('marketing.submissions') }}" class="btn btn-sm btn-secondary">
                    <i class="bi bi-x"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

@if($submissions->count() > 0)
<!-- Submissions Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode</th>
                        <th>Judul Artikel</th>
                        <th>Jurnal</th>
                        <th>Penulis</th>
                        <th>Tanggal Submit</th>
                        <th>Progress</th>
                        <th class="text-center">Edit Count</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($submissions as $submission)
                    @php
                        // Calculate progress based on workflow
                        $progress = 0;
                        if ($submission->petugas_submit_id) $progress += 10;
                        if ($submission->editor1_valid) $progress += 10;
                        if ($submission->author1_valid) $progress += 10;
                        if ($submission->editor2_valid) $progress += 15;
                        if ($submission->reviewer1_valid) $progress += 15;
                        if ($submission->reviewer2_valid) $progress += 15;
                        if ($submission->editor3_valid || !$submission->petugas_editor3_id) $progress += 10;
                        if ($submission->author2_valid || !$submission->petugas_author2_id) $progress += 5;
                        if ($submission->production_valid) $progress += 10;
                        
                        // Status badge color
                        $badgeColor = $submission->production_valid ? 'success' : 'warning';
                        $statusText = $submission->production_valid ? 'TERBIT' : 'PROSES';
                    @endphp
                    <tr class="{{ request('highlight') == $submission->id ? 'table-success' : '' }}">
                        <td>
                            <code class="text-primary">{{ $submission->kode_submit }}</code>
                            @if($submission->process_type === 'fasttrack')
                                <span class="badge bg-warning text-dark"><i class="bi bi-lightning-charge"></i> FT</span>
                            @endif
                            @if(request('highlight') == $submission->id)
                                <span class="badge bg-success ms-1">BARU</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ Str::limit($submission->judul_artikel, 60) }}</strong>
                        </td>
                        <td>
                            <small>{{ $submission->journalSlot?->journalMaster?->nama_jurnal ?? '-' }}</small>
                        </td>
                        <td>{{ $submission->nama_penulis }}</td>
                        <td>
                            <small>{{ $submission->tanggal_submit?->format('d/m/Y') ?? '-' }}</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-fill" style="height: 8px; min-width: 60px;">
                                    <div class="progress-bar bg-{{ $badgeColor }}" 
                                         style="width: {{ $progress }}%"></div>
                                </div>
                                <small class="text-{{ $badgeColor }} fw-bold" style="min-width: 35px;">
                                    {{ $progress }}%
                                </small>
                            </div>
                        </td>
                        <td class="text-center">
                            @php
                                $editCount = $submission->edit_count ?? 0;
                                $maxEditCount = 3;
                                $remainingEdits = $maxEditCount - $editCount;
                            @endphp
                            @if($editCount > 0)
                                <span class="badge {{ $remainingEdits == 0 ? 'bg-danger' : ($remainingEdits == 1 ? 'bg-warning text-dark' : 'bg-info') }}">
                                    {{ $editCount }}x
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('marketing.submissions.show', $submission) }}" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
<div class="d-flex justify-content-center mt-3">
    {{ $submissions->links() }}
</div>

@else
<div class="card">
    <div class="card-body">
        <div class="text-center text-muted py-5">
            <i class="bi bi-inbox" style="font-size: 4rem;"></i>
            <h5 class="mt-3">Belum Ada Artikel</h5>
            <p>Anda belum memiliki artikel yang disubmit.</p>
        </div>
    </div>
</div>
@endif
@endsection
