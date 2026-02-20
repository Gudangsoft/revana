@extends('marketing.layouts.app')

@section('title', 'Monitoring Fasttrack')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi bi-bar-chart text-warning"></i> Monitoring Fasttrack
    </h4>
    <a href="{{ route('marketing.fasttrack.create') }}" class="btn btn-warning">
        <i class="bi bi-plus-circle"></i> Input Fasttrack
    </a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $totalFasttrack }}</h3>
                <small>Total Fasttrack</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $thisMonthFasttrack }}</h3>
                <small>Bulan Ini</small>
            </div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" 
                       placeholder="Cari kode/judul/penulis..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-3 d-flex align-items-center gap-2">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-search"></i> Filter
                </button>
                @if(request()->hasAny(['search', 'from_date', 'to_date']))
                <a href="{{ route('marketing.fasttrack.monitoring') }}" class="btn btn-sm btn-secondary">
                    <i class="bi bi-x-circle"></i> Reset
                </a>
                @endif
                <div class="ms-auto d-flex align-items-center gap-1">
                    <small class="text-muted">Tampilkan:</small>
                    <select name="per_page" class="form-select form-select-sm" style="width: auto;">
                        @foreach([20, 50, 100, 150, 1000] as $pp)
                            <option value="{{ $pp }}" {{ request('per_page', 20) == $pp ? 'selected' : '' }}>{{ $pp }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Kode Submit</th>
                        <th>Jurnal</th>
                        <th>Judul Artikel</th>
                        <th>Penulis</th>
                        <th>Link Publish</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions as $submission)
                    <tr>
                        <td>{{ $loop->iteration + ($submissions->currentPage() - 1) * $submissions->perPage() }}</td>
                        <td>
                            <code class="text-warning">{{ $submission->kode_submit }}</code>
                            <br><span class="badge bg-warning text-dark"><i class="bi bi-lightning-charge"></i> Fasttrack</span>
                        </td>
                        <td>
                            @if($submission->journalSlot && $submission->journalSlot->journalMaster)
                                {{ Str::limit($submission->journalSlot->journalMaster->nama_jurnal, 20) }}
                                <br><small class="text-muted">
                                    {{ $submission->journalSlot->journalMaster->accreditation ?? '-' }}
                                </small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ Str::limit($submission->judul_artikel, 25) }}</td>
                        <td>{{ Str::limit($submission->nama_penulis, 15) }}</td>
                        <td>
                            @if($submission->link_publish)
                                <a href="{{ $submission->link_publish }}" target="_blank" class="btn btn-sm btn-success">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $submission->tanggal_submit ? $submission->tanggal_submit->format('d/m/Y') : '-' }}</td>
                        <td>
                            <a href="{{ route('marketing.fasttrack.show', $submission) }}" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-inbox display-6"></i>
                            <p class="mt-2">Belum ada data fasttrack</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('partials.per-page-selector', ['paginator' => $submissions])
@endsection


