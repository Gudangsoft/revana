@extends('pic.layouts.app')

@section('title', 'Tugas Saya')
@section('page-title', 'Tugas Saya')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('styles')
<style>
    .stat-card {
        border-radius: 10px;
        border: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    .stat-card .card-body {
        padding: 1.25rem;
    }
    .stat-card h3 {
        font-size: 2rem;
        font-weight: 700;
    }
    
    .progress-bar-custom {
        height: 8px;
        border-radius: 4px;
        background: #e9ecef;
        overflow: hidden;
    }
    .progress-bar-custom .fill {
        height: 100%;
        border-radius: 4px;
        transition: width 0.3s ease;
    }
    
    .table th {
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .table td {
        vertical-align: middle;
    }
    
    .badge-role {
        font-size: 0.7rem;
        padding: 0.35rem 0.6rem;
        font-weight: 500;
    }
    
    .kode-badge {
        font-family: monospace;
        background: #f8f9fa;
        padding: 0.4rem 0.6rem;
        border-radius: 6px;
        font-size: 0.85rem;
        color: #495057;
        border: 1px solid #dee2e6;
    }
    
    .badge-urgent {
        animation: pulse 1.5s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    
    .table-danger {
        --bs-table-bg: #fff5f5;
        border-left: 3px solid #dc3545;
    }
</style>
@endsection

@section('content')
<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-2">
        <div class="card stat-card border-start border-primary border-4">
            <div class="card-body">
                <div class="text-muted small mb-1">Total Tugas</div>
                <h3 class="text-primary mb-0">{{ $stats['total'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card stat-card border-start border-danger border-4">
            <div class="card-body">
                <div class="text-muted small mb-1">
                    <i class="bi bi-exclamation-triangle-fill text-danger"></i> Harus Dikerjakan
                </div>
                <h3 class="text-danger mb-0">{{ $stats['urgent'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card stat-card border-start border-info border-4">
            <div class="card-body">
                <div class="text-muted small mb-1">Baru</div>
                <h3 class="text-info mb-0">{{ $stats['new'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card border-start border-warning border-4">
            <div class="card-body">
                <div class="text-muted small mb-1">Dalam Proses</div>
                <h3 class="text-warning mb-0">{{ $stats['in_progress'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card border-start border-success border-4">
            <div class="card-body">
                <div class="text-muted small mb-1">Selesai</div>
                <h3 class="text-success mb-0">{{ $stats['published'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
</div>

@if(isset($stats['new_tasks']) && $stats['new_tasks'] > 0)
<div class="alert alert-info d-flex align-items-center mb-4 animate__animated animate__fadeIn" role="alert">
    <i class="bi bi-bell-fill me-2" style="font-size: 1.5rem;"></i>
    <div>
        <strong>🎉 Tugas Baru!</strong> Anda memiliki <strong>{{ $stats['new_tasks'] }}</strong> tugas baru yang ditugaskan kepada Anda.
        Tugas baru ditandai dengan <span class="badge bg-info"><i class="bi bi-star-fill"></i> Baru</span> pada tabel.
    </div>
</div>
@endif

@if(($stats['urgent'] ?? 0) > 0)
<div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
    <i class="bi bi-bell-fill me-2" style="font-size: 1.5rem;"></i>
    <div>
        <strong>Perhatian!</strong> Anda memiliki <strong>{{ $stats['urgent'] }}</strong> tugas yang harus segera dikerjakan.
        Tugas ditandai dengan <span class="badge bg-danger"><i class="bi bi-exclamation-circle"></i></span> pada tabel.
    </div>
</div>
@endif

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" 
                       placeholder="Cari kode, judul, penulis..." 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="SUBMITTED" {{ request('status') == 'SUBMITTED' ? 'selected' : '' }}>Baru</option>
                    <option value="EDITOR1_PROCESS" {{ request('status') == 'EDITOR1_PROCESS' ? 'selected' : '' }}>Editor 1</option>
                    <option value="REVIEWER1_PROCESS" {{ request('status') == 'REVIEWER1_PROCESS' ? 'selected' : '' }}>Review</option>
                    <option value="PUBLISHED" {{ request('status') == 'PUBLISHED' ? 'selected' : '' }}>Published</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Cari
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('pic.my-tasks.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-x"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-list-check"></i> Daftar Tugas Saya</h6>
        @include('partials.column-toggle', ['tableId' => 'picMyTasksTable', 'columns' => ['Kode', 'Artikel', 'Peran', 'Progress', 'Status', 'Aksi'], 'columnOffset' => 1])
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="picMyTasksTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px">#</th>
                        <th>Kode</th>
                        <th>Artikel</th>
                        <th>Peran</th>
                        <th style="width: 180px">Progress</th>
                        <th>Status</th>
                        <th style="width: 100px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions as $submission)
                    @php
                        // Progress calculation
                        $status = strtoupper($submission->status);
                        $stepIndex = 0;
                        $stepNames = ['NEW', 'EDITOR1', 'AUTHOR1', 'EDITOR2', 'REVIEWER1', 'REVIEWER2', 'EDITOR3', 'AUTHOR2', 'PRODUCTION', 'PUBLISHED'];
                        
                        foreach ($stepNames as $idx => $step) {
                            if (str_contains($status, $step)) {
                                $stepIndex = $idx;
                                break;
                            }
                        }
                        if ($status == 'PUBLISHED') $stepIndex = 9;
                        $progress = round(($stepIndex / 9) * 100);
                        
                        // My roles
                        $picId = auth()->guard('pic')->id();
                        $roles = [];
                        if ($submission->petugas_editor1_id == $picId) $roles[] = ['Editor 1', 'primary'];
                        if ($submission->petugas_editor2_id == $picId) $roles[] = ['Editor 2', 'primary'];
                        if ($submission->petugas_editor3_id == $picId) $roles[] = ['Editor 3', 'primary'];
                        if ($submission->petugas_author1_id == $picId) $roles[] = ['Author 1', 'info'];
                        if ($submission->petugas_author2_id == $picId) $roles[] = ['Author 2', 'info'];
                        if ($submission->petugas_production_id == $picId) $roles[] = ['Production', 'warning'];
                        if ($submission->petugas_submit_id == $picId) $roles[] = ['Submit', 'secondary'];
                        if (empty($roles) && $submission->created_by == $picId) $roles[] = ['Creator', 'dark'];
                        
                        // Progress bar color
                        $barColor = '#0d6efd';
                        if ($progress >= 100) $barColor = '#198754';
                        elseif ($progress >= 50) $barColor = '#0dcaf0';
                        
                        // Check if urgent - status matches PIC's assigned role
                        $isUrgent = false;
                        $urgentMappings = [
                            'NEW' => ['petugas_submit_id'],
                            'EDITOR1' => ['petugas_editor1_id'],
                            'AUTHOR1' => ['petugas_author1_id'],
                            'EDITOR2' => ['petugas_editor2_id'],
                            'REVIEWER1' => ['petugas_editor1_id', 'petugas_editor2_id'],
                            'REVIEWER2' => ['petugas_editor1_id', 'petugas_editor2_id'],
                            'EDITOR3' => ['petugas_editor3_id'],
                            'AUTHOR2' => ['petugas_author2_id'],
                            'PRODUCTION' => ['petugas_production_id'],
                        ];
                        foreach ($urgentMappings as $statusKey => $fields) {
                            if (str_contains($status, $statusKey)) {
                                foreach ($fields as $field) {
                                    if ($submission->$field == $picId) {
                                        $isUrgent = true;
                                        break 2;
                                    }
                                }
                            }
                        }
                        
                        // Check if this is a new task
                        $isNewTask = isset($newTaskIds) && in_array($submission->id, $newTaskIds);
                    @endphp
                    <tr class="{{ $isUrgent ? 'table-danger' : '' }} {{ $isNewTask ? 'table-info' : '' }}">
                        <td class="text-muted">
                            @if($isNewTask)
                                <span class="badge bg-info" title="Tugas Baru"><i class="bi bi-star-fill"></i> Baru</span>
                            @elseif($isUrgent)
                                <span class="badge bg-danger badge-urgent" title="Harus segera dikerjakan"><i class="bi bi-exclamation-circle"></i></span>
                            @endif
                            {{ $loop->iteration + ($submissions->currentPage() - 1) * $submissions->perPage() }}
                        </td>
                        <td>
                            <span class="kode-badge">{{ $submission->kode_submit }}</span>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ Str::limit($submission->judul_artikel, 45) }}</div>
                            <small class="text-muted">
                                <i class="bi bi-person"></i> {{ Str::limit($submission->nama_penulis, 25) }}
                                @if($submission->journalSlot && $submission->journalSlot->journalMaster)
                                    &bull; {{ Str::limit($submission->journalSlot->journalMaster->nama_jurnal, 20) }}
                                @endif
                            </small>
                        </td>
                        <td>
                            @foreach($roles as $role)
                                <span class="badge bg-{{ $role[1] }} badge-role">{{ $role[0] }}</span>
                            @endforeach
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress-bar-custom flex-grow-1">
                                    <div class="fill" style="width: {{ $progress }}%; background: {{ $barColor }};"></div>
                                </div>
                                <small class="text-muted" style="min-width: 35px">{{ $progress }}%</small>
                            </div>
                            <small class="text-muted">Step {{ $stepIndex + 1 }}/10</small>
                        </td>
                        <td>
                            @php
                                $statusBadge = match(strtolower($submission->status)) {
                                    'new' => 'info',
                                    'published' => 'success',
                                    default => 'secondary'
                                };
                                if (str_contains(strtolower($submission->status), 'editor')) $statusBadge = 'primary';
                                if (str_contains(strtolower($submission->status), 'author')) $statusBadge = 'warning';
                                if (str_contains(strtolower($submission->status), 'reviewer')) $statusBadge = 'info';
                                if (str_contains(strtolower($submission->status), 'production')) $statusBadge = 'dark';
                            @endphp
                            <span class="badge bg-{{ $statusBadge }}">
                                {{ str_replace('_', ' ', $submission->status) }}
                            </span>
                        </td>
                        <td>
                            @if($isUrgent)
                                <a href="{{ route('pic.submissions.process', $submission) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-play-fill"></i> Proses
                                </a>
                            @else
                                <a href="{{ route('pic.submissions.show', $submission) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2 mb-0">Belum ada tugas yang ditugaskan</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @include('partials.per-page-selector', ['paginator' => $submissions])
</div>
@endsection
