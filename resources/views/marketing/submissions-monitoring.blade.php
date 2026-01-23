@extends('marketing.layouts.app')

@section('title', 'Monitoring Slot')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi bi-bar-chart-line"></i> Monitoring Slot Jurnal
    </h4>
    <span class="badge bg-info fs-6">Total: {{ $submissions->total() }} submission</span>
</div>

<!-- Filter -->
<div class="card mb-3 shadow-sm border-0">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
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
                    <option value="UNDER_REVIEW" {{ request('status') == 'UNDER_REVIEW' ? 'selected' : '' }}>Under Review</option>
                    <option value="EDITING" {{ request('status') == 'EDITING' ? 'selected' : '' }}>Editing</option>
                    <option value="PRODUCTION" {{ request('status') == 'PRODUCTION' ? 'selected' : '' }}>Production</option>
                    <option value="PUBLISHED" {{ request('status') == 'PUBLISHED' ? 'selected' : '' }}>Published</option>
                    <option value="REJECTED" {{ request('status') == 'REJECTED' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="{{ route('marketing.submissions.monitoring') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </a>
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
            'in_review' => $submissions->whereIn('status', ['REVIEW_ASSIGNED', 'UNDER_REVIEW', 'REVISION_REQUIRED', 'REVISED'])->count(),
            'in_process' => $submissions->whereIn('status', ['EDITING', 'EDITING_SUBMITTED', 'EDITING_COMPLETED', 'LAYOUT', 'LAYOUT_SUBMITTED', 'LAYOUT_COMPLETED', 'PRODUCTION', 'PRODUCTION_SUBMITTED'])->count(),
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
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-3">Kode Submit</th>
                        <th>ID Artikel</th>
                        <th>Judul Artikel</th>
                        <th>Jurnal / Slot</th>
                        <th>Tanggal Submit</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Progress</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($submissions as $submission)
                    @php
                        $progressMap = [
                            'SUBMITTED' => 10,
                            'REVIEW_ASSIGNED' => 20,
                            'UNDER_REVIEW' => 30,
                            'REVISION_REQUIRED' => 35,
                            'REVISED' => 45,
                            'EDITING' => 55,
                            'EDITING_SUBMITTED' => 60,
                            'EDITING_COMPLETED' => 65,
                            'LAYOUT' => 70,
                            'LAYOUT_SUBMITTED' => 75,
                            'LAYOUT_COMPLETED' => 80,
                            'PRODUCTION' => 85,
                            'PRODUCTION_SUBMITTED' => 90,
                            'PUBLISHED' => 100,
                            'REJECTED' => 0,
                        ];
                        $progress = $progressMap[$submission->status] ?? 10;
                        
                        $badgeColor = match($submission->status) {
                            'SUBMITTED' => 'secondary',
                            'REVIEW_ASSIGNED', 'UNDER_REVIEW' => 'primary',
                            'REVISION_REQUIRED', 'REVISED' => 'warning',
                            'EDITING', 'EDITING_SUBMITTED', 'EDITING_COMPLETED' => 'info',
                            'LAYOUT', 'LAYOUT_SUBMITTED', 'LAYOUT_COMPLETED' => 'info',
                            'PRODUCTION', 'PRODUCTION_SUBMITTED' => 'dark',
                            'PUBLISHED' => 'success',
                            'REJECTED' => 'danger',
                            default => 'secondary'
                        };
                    @endphp
                    <tr>
                        <td class="px-3">
                            <code class="badge bg-light text-dark">{{ $submission->kode_submit }}</code>
                        </td>
                        <td>{{ $submission->id_artikel ?: '-' }}</td>
                        <td>
                            <div class="fw-semibold">{{ Str::limit($submission->judul_artikel, 40) }}</div>
                            <small class="text-muted">{{ $submission->nama_penulis }}</small>
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
                            <small>{{ $submission->tanggal_submit?->format('d M Y') ?? '-' }}</small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $badgeColor }} small">
                                {{ str_replace('_', ' ', $submission->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 8px; min-width: 80px;">
                                    <div class="progress-bar bg-{{ $badgeColor }}" 
                                         style="width: {{ $progress }}%"></div>
                                </div>
                                <small class="text-muted" style="min-width: 35px;">{{ $progress }}%</small>
                            </div>
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
        {{ $submissions->links() }}
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
