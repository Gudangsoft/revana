@extends('marketing.layouts.app')

@section('title', 'Monitoring Artikel' . (request('program') ? ' ' . strtoupper(request('program')) : ''))

@section('content')
@include('partials.auto-refresh', ['interval' => 30, 'arId' => 'marketing-mon'])

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi bi-bar-chart-line"></i> Monitoring Artikel{{ request('program') ? ' — ' . strtoupper(request('program')) : '' }}
    </h4>
    <span class="badge bg-info fs-6">Total: {{ $submissions->total() }} submission</span>
</div>

<!-- Filter -->
<div class="card mb-3 shadow-sm border-0">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <input type="hidden" name="program" value="{{ request('program') }}">
            <div class="col-md-3">
                <label class="form-label small mb-1">Cari</label>
                <input type="text" name="search" class="form-control"
                       placeholder="Kode/Judul/ID Artikel..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Slot Jurnal</label>
                <select name="journal_slot_id" class="form-select">
                    <option value="">Semua Slot</option>
                    @foreach($slots as $slot)
                    <option value="{{ $slot->id }}" {{ request('journal_slot_id') == $slot->id ? 'selected' : '' }}>
                        {{ $slot->journalMaster->nama_jurnal }} - {{ $slot->bulan }}/{{ $slot->tahun }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Status</label>
                <select name="status" class="form-select">
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
                    <option value="VALIDATOR_PROCESS" {{ request('status') == 'VALIDATOR_PROCESS' ? 'selected' : '' }}>Validasi</option>
                    <option value="PUBLISHED" {{ request('status') == 'PUBLISHED' ? 'selected' : '' }}>Published</option>
                    <option value="REJECTED" {{ request('status') == 'REJECTED' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-12 d-flex align-items-center gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="{{ route('marketing.submissions.monitoring') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </a>
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

@if($submissions->count() > 0)
<!-- Statistics Summary -->
<div class="row g-3 mb-4">
    @php
        $stats = [
            'submitted' => $submissions->where('status', 'SUBMITTED')->count(),
            'in_review' => $submissions->whereIn('status', ['EDITOR1_PROCESS', 'AUTHOR1_PROCESS', 'EDITOR2_PROCESS', 'REVIEWER1_PROCESS', 'REVIEWER2_PROCESS'])->count(),
            'in_process' => $submissions->whereIn('status', ['EDITOR3_PROCESS', 'AUTHOR2_PROCESS', 'PRODUCTION_PROCESS', 'VALIDATOR_PROCESS'])->count(),
            'published' => $submissions->where('status', 'PUBLISHED')->count(),
            'rejected' => $submissions->where('status', 'REJECTED')->count(),
        ];
    @endphp
    <div class="col-md-2">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white text-center">
                <h3 class="mb-0">{{ $stats['submitted'] }}</h3>
                <small class="opacity-75">Submitted</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="card-body text-white text-center">
                <h3 class="mb-0">{{ $stats['in_review'] }}</h3>
                <small class="opacity-75">Review</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="card-body text-white text-center">
                <h3 class="mb-0">{{ $stats['in_process'] }}</h3>
                <small class="opacity-75">Proses</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <div class="card-body text-white text-center">
                <h3 class="mb-0">{{ $stats['published'] }}</h3>
                <small class="opacity-75">Published</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-danger">
            <div class="card-body text-white text-center">
                <h3 class="mb-0">{{ $stats['rejected'] }}</h3>
                <small class="opacity-75">Rejected</small>
            </div>
        </div>
    </div>
</div>

<!-- Submissions Table -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        @include('partials.column-toggle', ['tableId' => 'mktSubmMonTable', 'columns' => ['Kode Submit', 'ID Artikel', 'Judul Artikel', 'No HP', 'Jurnal / Slot', 'Akreditasi', 'Tanggal Submit', 'Status', 'Progress', 'Aksi'], 'columnOffset' => 0])
        <div class="table-responsive">
            <table id="mktSubmMonTable" class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-3">Kode Submit</th>
                        <th>ID Artikel</th>
                        <th>Judul Artikel</th>
                        <th>No HP</th>
                        <th>Jurnal / Slot</th>
                        <th>Akreditasi</th>
                        <th>Tanggal Submit</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Progress</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($submissions as $submission)
                    <tr>
                        <td class="px-3">
                            <code class="badge bg-light text-dark">{{ $submission->kode_submit }}</code>
                            @if($submission->journalSlot)
                                <br><small class="text-muted" style="font-size: 0.65rem; line-height: 1.2;" title="{{ $submission->journalSlot->journalMaster?->nama_jurnal ?? '-' }} - Vol.{{ $submission->journalSlot->volume }} No.{{ $submission->journalSlot->nomor }}">{{ Str::limit($submission->journalSlot->journalMaster?->nama_jurnal ?? '-', 20) }}<br>Vol.{{ $submission->journalSlot->volume }} No.{{ $submission->journalSlot->nomor }}</small>
                            @endif
                            @if($submission->process_type === 'fasttrack')
                                <span class="badge bg-warning text-dark"><i class="bi bi-lightning-charge"></i> FT</span>
                            @endif
                        </td>
                        <td>{{ $submission->id_artikel ?: '-' }}</td>
                        <td>
                            <div class="fw-semibold">{{ Str::limit($submission->judul_artikel, 40) }}</div>
                            <small class="text-muted">{{ $submission->nama_penulis }}</small>
                        </td>
                        <td>
                            <small>{{ $submission->no_hp_penulis ?? '-' }}</small>
                        </td>
                        <td>
                            <small class="text-primary fw-semibold">
                                {{ $submission->journalSlot?->journalMaster?->nama_jurnal ?? '-' }}
                            </small><br>
                            <small class="text-muted">
                                {{ $submission->journalSlot ? $submission->journalSlot->bulan . '/' . $submission->journalSlot->tahun : '-' }}
                            </small>
                        </td>
                        <td>
                            @if($submission->journalSlot?->journalMaster?->accreditation)
                                <span class="badge bg-info">{{ $submission->journalSlot->journalMaster->accreditation }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <small>{{ $submission->tanggal_submit?->format('d M Y') ?? '-' }}</small>
                        </td>
                        <td class="text-center">
                            <x-submission-status :submission="$submission" size="small" />
                        </td>
                        <td>
                            <x-submission-progress :submission="$submission" :height="8" :min-width="80" />
                        </td>
                        <td class="text-center">
                            <a href="{{ route('marketing.submissions.show', $submission) }}" 
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
        <p class="text-muted mt-3 mb-0">Tidak ada data submission</p>
    </div>
</div>
@endif

@endsection
