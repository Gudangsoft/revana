@extends('layouts.app')

@section('title', 'Histori Proses - ' . $submission->kode_submit)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Histori Proses</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.submissions.index') }}">Data Submit</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.submissions.process', $submission) }}">Proses</a></li>
                            <li class="breadcrumb-item active">Histori</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('admin.submissions.process', $submission) }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali ke Proses
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Submission -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <small class="text-muted">Kode Submit</small>
                            <p class="mb-0 fw-bold">{{ $submission->kode_submit }}</p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Jurnal</small>
                            <p class="mb-0">{{ $submission->journalSlot?->journalMaster?->nama_jurnal ?? '-' }}</p>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Judul Artikel</small>
                            <p class="mb-0">{{ Str::limit($submission->judul_artikel, 50) }}</p>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">Status</small>
                            <p class="mb-0">
                                <span class="badge {{ $submission->status_badge_class }}">
                                    {{ $submission->status_label }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline History -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock-history"></i> Timeline Histori Proses
                    </h5>
                </div>
                <div class="card-body">
                    @if($submission->histories->count() > 0)
                        <div class="timeline">
                            @foreach($submission->histories->sortByDesc('created_at') as $history)
                                <div class="timeline-item mb-3">
                                    <div class="d-flex align-items-start">
                                        <div class="timeline-badge me-3">
                                            @switch($history->action)
                                                @case('assigned')
                                                    <span class="badge rounded-pill bg-info"><i class="bi bi-person-plus"></i></span>
                                                    @break
                                                @case('submitted')
                                                    <span class="badge rounded-pill bg-primary"><i class="bi bi-send"></i></span>
                                                    @break
                                                @case('revision_request')
                                                    <span class="badge rounded-pill bg-warning"><i class="bi bi-arrow-return-left"></i></span>
                                                    @break
                                                @case('revision_submit')
                                                    <span class="badge rounded-pill bg-secondary"><i class="bi bi-arrow-return-right"></i></span>
                                                    @break
                                                @case('approved')
                                                    <span class="badge rounded-pill bg-success"><i class="bi bi-check-lg"></i></span>
                                                    @break
                                                @case('rejected')
                                                    <span class="badge rounded-pill bg-danger"><i class="bi bi-x-lg"></i></span>
                                                    @break
                                                @case('note_added')
                                                    <span class="badge rounded-pill bg-light text-dark"><i class="bi bi-sticky"></i></span>
                                                    @break
                                                @case('credential_added')
                                                    <span class="badge rounded-pill bg-dark"><i class="bi bi-key"></i></span>
                                                    @break
                                                @default
                                                    <span class="badge rounded-pill bg-secondary"><i class="bi bi-circle"></i></span>
                                            @endswitch
                                        </div>
                                        <div class="timeline-content flex-grow-1">
                                            <div class="card border-0 shadow-sm">
                                                <div class="card-body py-2 px-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <div>
                                                            <span class="badge {{ $history->step_badge_class }} me-2">
                                                                {{ $history->step_label }}
                                                            </span>
                                                            <span class="badge {{ $history->action_badge_class }}">
                                                                {{ $history->action_label }}
                                                                @if($history->revision_number > 0)
                                                                    <span class="ms-1">#{{ $history->revision_number }}</span>
                                                                @endif
                                                            </span>
                                                        </div>
                                                        <small class="text-muted">
                                                            {{ $history->created_at->format('d M Y H:i') }}
                                                        </small>
                                                    </div>
                                                    @if($history->notes)
                                                        <p class="mb-1 small">{{ $history->notes }}</p>
                                                    @endif
                                                    <small class="text-muted">
                                                        <i class="bi bi-person"></i> 
                                                        {{ $history->user->name ?? 'System' }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-clock-history display-4 text-muted"></i>
                            <p class="text-muted mt-2">Belum ada histori proses</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- History by Step -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-diagram-3"></i> Histori per Tahap
                    </h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="accordionHistoryByStep">
                        @php
                            $steps = ['submit', 'editor1', 'author1', 'editor2', 'reviewer1', 'reviewer2', 'editor3', 'author2', 'production'];
                        @endphp
                        
                        @foreach($steps as $step)
                            @php
                                $stepHistories = $historiesByStep[$step] ?? collect();
                                $revisionCount = $stepHistories->where('action', 'revision_request')->count();
                            @endphp
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading{{ $step }}">
                                    <button class="accordion-button {{ $stepHistories->isEmpty() ? 'collapsed' : '' }}" type="button" 
                                            data-bs-toggle="collapse" data-bs-target="#collapse{{ $step }}" 
                                            aria-expanded="{{ $stepHistories->isNotEmpty() ? 'true' : 'false' }}" 
                                            aria-controls="collapse{{ $step }}">
                                        <span class="badge {{ \App\Models\SubmissionHistory::getStepBadgeClass($step) }} me-2">
                                            {{ \App\Models\SubmissionHistory::getStepLabel($step) }}
                                        </span>
                                        <span class="text-muted small">
                                            ({{ $stepHistories->count() }} entri
                                            @if($revisionCount > 0)
                                                , <span class="text-warning">{{ $revisionCount }} revisi</span>
                                            @endif
                                            )
                                        </span>
                                    </button>
                                </h2>
                                <div id="collapse{{ $step }}" class="accordion-collapse collapse {{ $stepHistories->isNotEmpty() ? 'show' : '' }}" 
                                     aria-labelledby="heading{{ $step }}" data-bs-parent="#accordionHistoryByStep">
                                    <div class="accordion-body">
                                        @if($stepHistories->isNotEmpty())
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th width="150">Waktu</th>
                                                            <th width="120">Aksi</th>
                                                            <th>Catatan</th>
                                                            <th width="150">Oleh</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($stepHistories as $h)
                                                            <tr>
                                                                <td>{{ $h->created_at->format('d/m/Y H:i') }}</td>
                                                                <td>
                                                                    <span class="badge {{ $h->action_badge_class }}">
                                                                        {{ $h->action_label }}
                                                                        @if($h->revision_number > 0)
                                                                            #{{ $h->revision_number }}
                                                                        @endif
                                                                    </span>
                                                                </td>
                                                                <td>{{ $h->notes ?: '-' }}</td>
                                                                <td>{{ $h->user->name ?? 'System' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <p class="text-muted mb-0">Belum ada aktivitas pada tahap ini.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline-badge {
    min-width: 32px;
}
.timeline-badge .badge {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endsection
