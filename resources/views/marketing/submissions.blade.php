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
                    <option value="EDITOR1_PROCESS" {{ request('status') == 'EDITOR1_PROCESS' ? 'selected' : '' }}>Editor 1</option>
                    <option value="AUTHOR1_PROCESS" {{ request('status') == 'AUTHOR1_PROCESS' ? 'selected' : '' }}>Author 1</option>
                    <option value="EDITOR2_PROCESS" {{ request('status') == 'EDITOR2_PROCESS' ? 'selected' : '' }}>Editor 2</option>
                    <option value="REVIEWER1_PROCESS" {{ request('status') == 'REVIEWER1_PROCESS' ? 'selected' : '' }}>Reviewer 1</option>
                    <option value="REVIEWER2_PROCESS" {{ request('status') == 'REVIEWER2_PROCESS' ? 'selected' : '' }}>Reviewer 2</option>
                    <option value="EDITOR3_PROCESS" {{ request('status') == 'EDITOR3_PROCESS' ? 'selected' : '' }}>Editor 3</option>
                    <option value="AUTHOR2_PROCESS" {{ request('status') == 'AUTHOR2_PROCESS' ? 'selected' : '' }}>Author 2</option>
                    <option value="PRODUCTION_PROCESS" {{ request('status') == 'PRODUCTION_PROCESS' ? 'selected' : '' }}>Production</option>
                    <option value="VALIDATOR_PROCESS" {{ request('status') == 'VALIDATOR_PROCESS' ? 'selected' : '' }}>Validator</option>
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
            <div class="col-md-1">
                <select name="per_page" class="form-select form-select-sm" title="Jumlah data per halaman">
                    @foreach([10, 50, 100, 150, 1000] as $pp)
                        <option value="{{ $pp }}" {{ request('per_page', 10) == $pp ? 'selected' : '' }}>{{ $pp }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
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
        <div class="px-3 pt-2 d-flex justify-content-end">
            @include('partials.column-toggle', ['tableId' => 'mktSubmTable', 'columns' => ['Kode', 'Judul Artikel', 'Jurnal', 'Penulis', 'Tanggal Submit', 'Status', 'Progress', 'Edit Count', 'Aksi'], 'columnOffset' => 0])
        </div>
        <div class="table-responsive">
            <table id="mktSubmTable" class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode</th>
                        <th>Judul Artikel</th>
                        <th>Jurnal</th>
                        <th>Penulis</th>
                        <th>Tanggal Submit</th>
                        <th class="text-center">Status</th>
                        <th>Progress</th>
                        <th class="text-center">Edit Count</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($submissions as $submission)
                    <tr class="{{ request('highlight') == $submission->id ? 'table-success' : '' }}">
                        <td>
                            <code class="text-primary">{{ $submission->kode_submit }}</code>
                            @if($submission->journalSlot)
                                <br><small class="text-muted" style="font-size: 0.65rem; line-height: 1.2;" title="{{ $submission->journalSlot->journalMaster?->nama_jurnal ?? '-' }} - Vol.{{ $submission->journalSlot->volume }} No.{{ $submission->journalSlot->nomor }}">{{ Str::limit($submission->journalSlot->journalMaster?->nama_jurnal ?? '-', 20) }}<br>Vol.{{ $submission->journalSlot->volume }} No.{{ $submission->journalSlot->nomor }}</small>
                            @endif
                            @if($submission->process_type === 'fasttrack')
                                <br><span class="badge bg-warning text-dark"><i class="bi bi-lightning-charge"></i> FT</span>
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
                        <td class="text-center">
                            <x-submission-status :submission="$submission" size="small" />
                        </td>
                        <td>
                            <x-submission-progress :submission="$submission" />
                        </td>
                        <td class="text-center">
                            @php
                                $editCount = $submission->edit_count ?? 0;
                                $maxEditCount = \App\Services\FeatureSettingService::limit('max_fasttrack_edits');
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

@include('partials.per-page-selector', ['paginator' => $submissions, 'default' => 10])

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

