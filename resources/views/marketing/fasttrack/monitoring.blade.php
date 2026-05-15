@extends('marketing.layouts.app')

@section('title', 'Monitoring Fasttrack')

@section('content')
@include('partials.auto-refresh', ['interval' => 30, 'arId' => 'mkt-ft-mon'])

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi bi-lightning-charge-fill text-warning"></i> Monitoring Fasttrack
    </h4>
    <div class="d-flex gap-2 align-items-center">
        <span class="badge bg-info fs-6">Total: {{ $submissions->total() }} submission</span>
        <a href="{{ route('marketing.fasttrack.create') }}" class="btn btn-warning btn-sm">
            <i class="bi bi-plus-circle me-1"></i>Input Fasttrack
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Filter --}}
<div class="card mb-3 shadow-sm border-0">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small mb-1">Cari</label>
                <input type="text" name="search" class="form-control"
                       placeholder="Kode, judul, penulis..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Dari Tanggal</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Sampai Tanggal</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="{{ route('marketing.fasttrack.monitoring') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </a>
                <div class="ms-auto d-flex align-items-center gap-1">
                    <small class="text-muted">Tampilkan:</small>
                    <select name="per_page" class="form-select form-select-sm" style="width:auto;"
                            onchange="this.form.submit()">
                        @foreach([20, 50, 100, 150, 1000] as $pp)
                            <option value="{{ $pp }}" {{ request('per_page', 20) == $pp ? 'selected' : '' }}>{{ $pp }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Stats Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm"
             style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
            <div class="card-body text-white text-center py-3">
                <h3 class="mb-0 fw-bold">{{ $totalFasttrack }}</h3>
                <small class="opacity-75">Total Fasttrack</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm"
             style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="card-body text-white text-center py-3">
                <h3 class="mb-0 fw-bold">{{ $thisMonthFasttrack }}</h3>
                <small class="opacity-75">Bulan Ini</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm"
             style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <div class="card-body text-white text-center py-3">
                @php
                    $publishedCount = \App\Models\Submission::where('process_type','fasttrack')
                        ->where('marketing_id', auth()->guard('marketing')->id())
                        ->where('status','PUBLISHED')->count();
                @endphp
                <h3 class="mb-0 fw-bold">{{ $publishedCount }}</h3>
                <small class="opacity-75">Published</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm"
             style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white text-center py-3">
                @php
                    $pendingCount = $totalFasttrack - $publishedCount;
                @endphp
                <h3 class="mb-0 fw-bold">{{ $pendingCount }}</h3>
                <small class="opacity-75">Belum Published</small>
            </div>
        </div>
    </div>
</div>

@if($submissions->count() > 0)
{{-- Table --}}
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        @include('partials.column-toggle', [
            'tableId'      => 'mktFtMonTable',
            'columns'      => ['Kode Submit', 'Judul Artikel', 'Jurnal / Slot', 'Akreditasi', 'Tanggal Submit', 'Link Publish', 'Status', 'Aksi'],
            'columnOffset' => 0
        ])
        <div class="table-responsive">
            <table id="mktFtMonTable" class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-3">Kode Submit</th>
                        <th>Judul Artikel</th>
                        <th>Jurnal / Slot</th>
                        <th>Akreditasi</th>
                        <th>Tanggal Submit</th>
                        <th class="text-center">Link Publish</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($submissions as $submission)
                    <tr>
                        <td class="px-3">
                            <code class="badge bg-light text-dark">{{ $submission->kode_submit }}</code>
                            <br>
                            <span class="badge bg-warning text-dark mt-1">
                                <i class="bi bi-lightning-charge"></i> Fasttrack
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ Str::limit($submission->judul_artikel, 45) }}</div>
                            <small class="text-muted">{{ $submission->nama_penulis }}</small>
                        </td>
                        <td>
                            <small class="text-primary fw-semibold">
                                {{ $submission->journalSlot?->journalMaster?->nama_jurnal ?? '-' }}
                            </small>
                            @if($submission->journalSlot)
                            <br><small class="text-muted">
                                Vol.{{ $submission->journalSlot->volume }}
                                No.{{ $submission->journalSlot->nomor }}
                                ({{ $submission->journalSlot->bulan }}/{{ $submission->journalSlot->tahun }})
                            </small>
                            @endif
                        </td>
                        <td>
                            @if($submission->journalSlot?->journalMaster?->accreditation)
                                <span class="badge bg-info">
                                    {{ $submission->journalSlot->journalMaster->accreditation }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <small>{{ $submission->tanggal_submit?->format('d M Y') ?? '-' }}</small>
                        </td>
                        <td class="text-center">
                            @if($submission->link_publish)
                                <a href="{{ $submission->link_publish }}" target="_blank"
                                   class="btn btn-sm btn-success" title="{{ $submission->link_publish }}">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>Lihat
                                </a>
                            @else
                                <span class="text-muted small">Belum ada</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <x-submission-status :submission="$submission" size="small" />
                        </td>
                        <td class="text-center">
                            <a href="{{ route('marketing.fasttrack.show', $submission) }}"
                               class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        @include('partials.per-page-selector', ['paginator' => $submissions])
    </div>
</div>
@else
<div class="card shadow-sm border-0">
    <div class="card-body text-center py-5">
        <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
        <p class="text-muted mt-3 mb-0">Tidak ada data fasttrack</p>
        <a href="{{ route('marketing.fasttrack.create') }}" class="btn btn-warning mt-3">
            <i class="bi bi-plus-circle me-1"></i>Input Fasttrack Pertama
        </a>
    </div>
</div>
@endif

@endsection
