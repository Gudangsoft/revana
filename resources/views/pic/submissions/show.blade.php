@extends('pic.layouts.app')

@section('title', 'Detail Submission')
@section('page-title', 'Detail Submission')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
@if($canProcess ?? false)
<div class="alert alert-warning d-flex align-items-center mb-3">
    <i class="bi bi-exclamation-triangle-fill me-2" style="font-size: 1.5rem;"></i>
    <div class="flex-grow-1">
        <strong>Tugas Anda!</strong> Submission ini membutuhkan aksi dari Anda sebagai <strong>{{ $currentRole ?? 'PIC' }}</strong>.
    </div>
    <a href="{{ route('pic.submissions.process', $submission) }}" class="btn btn-warning">
        <i class="bi bi-play-fill"></i> Proses Sekarang
    </a>
</div>
@endif

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-file-earmark-text"></i> Detail Submission</span>
        <span class="badge {{ $submission->status_badge_class }}">
            {{ $submission->status_label }}
        </span>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <strong>Kode Submit:</strong>
                <div><code>{{ $submission->kode_submit }}</code></div>
            </div>
            <div class="col-md-4">
                <strong>Kode LOA:</strong>
                <div><code>{{ $submission->kode_loa }}</code></div>
            </div>
            <div class="col-md-4">
                <strong>Tanggal Submit:</strong>
                <div>{{ $submission->tanggal_submit ? \Carbon\Carbon::parse($submission->tanggal_submit)->format('d F Y') : $submission->created_at->format('d F Y') }}</div>
            </div>
        </div>
        
        <hr>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Jurnal:</strong>
                <div>
                    @if($submission->journalSlot && $submission->journalSlot->journalMaster)
                        {{ $submission->journalSlot->journalMaster->nama_jurnal }}
                    @else
                        -
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <strong>Slot:</strong>
                <div>
                    @if($submission->journalSlot)
                        Vol. {{ $submission->journalSlot->volume }} No. {{ $submission->journalSlot->nomor }} ({{ $submission->journalSlot->tahun }})
                    @else
                        -
                    @endif
                </div>
            </div>
        </div>
        
        <hr>
        
        <div class="mb-3">
            <strong>ID Artikel:</strong>
            <div>{{ $submission->id_artikel ?? '-' }}</div>
        </div>
        
        <div class="mb-3">
            <strong>Judul Artikel:</strong>
            <div class="text-primary fw-bold">{{ $submission->judul_artikel }}</div>
        </div>
        
        @if($submission->link_artikel)
        <div class="mb-3">
            <strong>Link Submit:</strong>
            <div><a href="{{ $submission->link_artikel }}" target="_blank">{{ $submission->link_artikel }}</a></div>
        </div>
        @endif
        
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Nama Penulis:</strong>
                <div>{{ $submission->nama_penulis }}</div>
            </div>
            <div class="col-md-6">
                <strong>No. HP Penulis:</strong>
                <div>{{ $submission->no_hp_penulis ?? '-' }}</div>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Username Author:</strong>
                <div>{{ $submission->username_author ?? '-' }}</div>
            </div>
            <div class="col-md-6">
                <strong>Password Author:</strong>
                <div>{{ $submission->password_author ?? '-' }}</div>
            </div>
        </div>
        
        @if($submission->notes)
        <hr>
        <div class="mb-3">
            <strong>Catatan:</strong>
            <div class="text-muted">{{ $submission->notes }}</div>
        </div>
        @endif

        @if($submission->catatan_marketing)
        <hr>
        <div class="mb-3">
            <strong><i class="bi bi-chat-left-text text-warning"></i> Catatan dari Marketing:</strong>
            <div class="alert alert-warning mt-1 mb-0">{{ $submission->catatan_marketing }}</div>
        </div>
        @endif

        @php
            // Kumpulkan catatan per role dari histories (note_added)
            $notesByStep = [];
            if ($submission->histories) {
                foreach ($submission->histories->where('action', 'note_added')->sortByDesc('created_at') as $nh) {
                    if (!isset($notesByStep[$nh->step])) {
                        $notesByStep[$nh->step] = $nh->notes;
                    }
                }
            }
            // Tambahkan catatan_reviewer1 dan catatan_reviewer2 dari kolom submissions
            if ($submission->catatan_reviewer1) $notesByStep['reviewer1'] = $submission->catatan_reviewer1;
            if ($submission->catatan_reviewer2) $notesByStep['reviewer2'] = $submission->catatan_reviewer2;

            $stepLabels = [
                'editor1' => 'Editor 1', 'author1' => 'Author 1', 'editor2' => 'Editor 2',
                'reviewer1' => 'Reviewer 1', 'reviewer2' => 'Reviewer 2',
                'editor3' => 'Editor 3', 'author2' => 'Author 2', 'production' => 'Production',
            ];
        @endphp

        @if(count($notesByStep) > 0)
        <hr>
        <div class="mb-3">
            <strong><i class="bi bi-chat-left-text text-info"></i> Catatan per Tahap:</strong>
            @foreach($notesByStep as $step => $noteText)
            <div class="alert alert-info mt-2 mb-1 py-2">
                <small class="fw-bold text-info">{{ $stepLabels[$step] ?? $step }}:</small>
                <div>{{ $noteText }}</div>
            </div>
            @endforeach
        </div>
        @endif
        
        @if($submission->link_publish)
        <hr>
        <div class="mb-3">
            <strong>Link Publish:</strong>
            <div><a href="{{ $submission->link_publish }}" target="_blank" class="btn btn-sm btn-success">
                <i class="bi bi-link-45deg"></i> Lihat Publikasi
            </a></div>
        </div>
        @endif
    </div>
</div>

<!-- Workflow Progress -->
<div class="card mb-3">
    <div class="card-header">
        <i class="bi bi-diagram-3"></i> Progress Workflow
    </div>
    <div class="card-body">
        <div class="row text-center">
            <div class="col">
                <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center {{ $submission->editor1_valid ? 'bg-success' : 'bg-secondary' }}" style="width: 40px; height: 40px;">
                    <i class="bi bi-check text-white"></i>
                </div>
                <small>Editor 1</small>
            </div>
            <div class="col">
                <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center {{ $submission->author1_valid ? 'bg-success' : 'bg-secondary' }}" style="width: 40px; height: 40px;">
                    <i class="bi bi-check text-white"></i>
                </div>
                <small>Author 1</small>
            </div>
            <div class="col">
                <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center {{ $submission->editor2_valid ? 'bg-success' : 'bg-secondary' }}" style="width: 40px; height: 40px;">
                    <i class="bi bi-check text-white"></i>
                </div>
                <small>Editor 2</small>
            </div>
            <div class="col">
                <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center {{ $submission->reviewer1_valid ? 'bg-success' : 'bg-secondary' }}" style="width: 40px; height: 40px;">
                    <i class="bi bi-check text-white"></i>
                </div>
                <small>Reviewer 1</small>
            </div>
            <div class="col">
                <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center {{ $submission->reviewer2_valid ? 'bg-success' : 'bg-secondary' }}" style="width: 40px; height: 40px;">
                    <i class="bi bi-check text-white"></i>
                </div>
                <small>Reviewer 2</small>
            </div>
            <div class="col">
                <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center {{ $submission->editor3_valid ? 'bg-success' : 'bg-secondary' }}" style="width: 40px; height: 40px;">
                    <i class="bi bi-check text-white"></i>
                </div>
                <small>Editor 3</small>
            </div>
            <div class="col">
                <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center {{ $submission->author2_valid ? 'bg-success' : 'bg-secondary' }}" style="width: 40px; height: 40px;">
                    <i class="bi bi-check text-white"></i>
                </div>
                <small>Author 2</small>
            </div>
            <div class="col">
                <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center {{ $submission->production_valid ? 'bg-success' : 'bg-secondary' }}" style="width: 40px; height: 40px;">
                    <i class="bi bi-check text-white"></i>
                </div>
                <small>Production</small>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-start">
    <a href="{{ route('pic.submissions.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali ke Daftar
    </a>
</div>
@endsection
