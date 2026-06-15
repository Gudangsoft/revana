@extends('pic.layouts.app')

@section('title', 'Detail Fasttrack')
@section('page-title', 'Detail Fasttrack')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
@php
    $editCount    = $submission->edit_count ?? 0;
    $maxEditCount = \App\Services\FeatureSettingService::limit('max_fasttrack_edits');
    $canEdit      = $editCount < $maxEditCount;

    // Catatan per tahap dari histories
    $notesByStep = [];
    if ($submission->histories) {
        foreach ($submission->histories->where('action', 'note_added')->sortByDesc('created_at') as $nh) {
            if (!isset($notesByStep[$nh->step])) $notesByStep[$nh->step] = $nh->notes;
        }
    }
@endphp

{{-- Action bar --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-warning text-dark fs-6">
            <i class="bi bi-lightning-charge-fill"></i> Fasttrack
        </span>
        <x-submission-status :submission="$submission" />
        @if($editCount > 0)
            <span class="badge {{ ($maxEditCount - $editCount) == 0 ? 'bg-danger' : (($maxEditCount - $editCount) == 1 ? 'bg-warning text-dark' : 'bg-info') }}">
                <i class="bi bi-pencil"></i> Diedit {{ $editCount }}x
            </span>
        @endif
    </div>
    <div class="d-flex gap-2">
        @if($canEdit)
            <a href="{{ route('pic.fasttrack.edit', $submission) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-pencil-square"></i> Edit ({{ $maxEditCount - $editCount }}x tersisa)
            </a>
        @else
            <button class="btn btn-secondary btn-sm" disabled>
                <i class="bi bi-lock"></i> Edit Terkunci
            </button>
        @endif
        <a href="{{ route('pic.fasttrack.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
</div>

{{-- Main Detail Card --}}
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-file-earmark-text"></i> Detail Submission Fasttrack</span>
        <code class="text-warning fw-bold">{{ $submission->kode_submit }}</code>
    </div>
    <div class="card-body">

        {{-- Kode & Tanggal --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <strong>Kode Submit:</strong>
                <div><code>{{ $submission->kode_submit }}</code></div>
            </div>
            <div class="col-md-4">
                <strong>Kode LOA:</strong>
                <div><code>{{ $submission->kode_loa ?? '-' }}</code></div>
            </div>
            <div class="col-md-4">
                <strong>Tanggal Submit:</strong>
                <div>{{ $submission->tanggal_submit ? \Carbon\Carbon::parse($submission->tanggal_submit)->format('d F Y') : $submission->created_at->format('d F Y') }}</div>
            </div>
        </div>

        <hr>

        {{-- Jurnal & Slot --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Jurnal:</strong>
                <div>
                    @if($submission->journalSlot?->journalMaster)
                        {{ $submission->journalSlot->journalMaster->nama_jurnal }}
                        @if($submission->journalSlot->journalMaster->accreditation)
                            <span class="badge bg-info ms-1">{{ $submission->journalSlot->journalMaster->accreditation }}</span>
                        @endif
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <strong>Slot:</strong>
                <div>
                    @if($submission->journalSlot)
                        Vol. {{ $submission->journalSlot->volume }}
                        No. {{ $submission->journalSlot->nomor }}
                        ({{ $submission->journalSlot->bulan }}/{{ $submission->journalSlot->tahun }})
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </div>
            </div>
        </div>

        <hr>

        {{-- Artikel --}}
        @if($submission->id_artikel)
        <div class="mb-3">
            <strong>ID Artikel:</strong>
            <div>{{ $submission->id_artikel }}</div>
        </div>
        @endif

        <div class="mb-3">
            <strong>Judul Artikel:</strong>
            <div class="text-primary fw-bold">{{ $submission->judul_artikel }}</div>
        </div>

        {{-- Link Submit --}}
        @if($submission->link_artikel)
        <div class="mb-3">
            <strong>Link Submit:</strong>
            <div>
                <a href="{{ $submission->link_artikel }}" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-box-arrow-up-right"></i> Buka Link Submit
                </a>
                <br><small class="text-muted">{{ $submission->link_artikel }}</small>
            </div>
        </div>
        @endif

        <hr>

        {{-- Data Penulis --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Nama Penulis:</strong>
                <div>{{ $submission->nama_penulis }}</div>
            </div>
            <div class="col-md-6">
                <strong>No. HP Penulis:</strong>
                <div>{{ $submission->no_hp_penulis ?? '-' }}</div>
            </div>
            <div class="col-md-6 mt-2">
                <strong>Email Penulis:</strong>
                <div>{{ $submission->email_penulis ?? '-' }}</div>
            </div>
        </div>

        {{-- Username / Password Author --}}
        @if($submission->username_author || $submission->password_author)
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
        @endif

        <hr>

        {{-- PIC & Marketing --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Marketing:</strong>
                <div>{{ $submission->marketing->name ?? '-' }}</div>
            </div>
            <div class="col-md-6">
                <strong>PIC Submit:</strong>
                <div>{{ $submission->petugasSubmit->name ?? '-' }}</div>
            </div>
        </div>

        {{-- Catatan --}}
        @if($submission->notes)
        <hr>
        <div class="mb-3">
            <strong>Catatan:</strong>
            <div class="text-muted">{{ $submission->notes }}</div>
        </div>
        @endif

        {{-- Catatan Marketing --}}
        @if($submission->catatan_marketing)
        <hr>
        <div class="mb-3">
            <strong><i class="bi bi-chat-left-text text-warning"></i> Catatan dari Marketing:</strong>
            <div class="alert alert-warning mt-1 mb-0">{{ $submission->catatan_marketing }}</div>
        </div>
        @endif

        {{-- Catatan per tahap --}}
        @if(count($notesByStep) > 0)
        <hr>
        <div class="mb-3">
            <strong><i class="bi bi-chat-left-text text-info"></i> Catatan per Tahap:</strong>
            @foreach($notesByStep as $step => $noteText)
            <div class="alert alert-info mt-2 mb-1 py-2">
                <small class="fw-bold text-info">{{ ucfirst($step) }}:</small>
                <div>{{ $noteText }}</div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Link Publish --}}
        @if($submission->link_publish)
        <hr>
        <div class="mb-3">
            <strong>Link Publish:</strong>
            <div>
                <a href="{{ $submission->link_publish }}" target="_blank" class="btn btn-sm btn-success">
                    <i class="bi bi-link-45deg"></i> Lihat Publikasi
                </a>
                <br><small class="text-muted">{{ $submission->link_publish }}</small>
            </div>
        </div>
        @endif

    </div>
</div>

{{-- History --}}
@if($submission->histories && $submission->histories->count() > 0)
<div class="card mb-3">
    <div class="card-header">
        <i class="bi bi-clock-history"></i> Riwayat Perubahan
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Waktu</th>
                        <th>Step</th>
                        <th>Action</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($submission->histories->sortByDesc('created_at') as $history)
                    <tr>
                        <td class="text-nowrap">{{ $history->created_at->format('d/m/Y H:i') }}</td>
                        <td><span class="badge bg-secondary">{{ $history->step }}</span></td>
                        <td>{{ $history->action }}</td>
                        <td class="text-muted">{{ $history->notes ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<div class="d-flex justify-content-start">
    <a href="{{ route('pic.fasttrack.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali ke Daftar
    </a>
</div>
@endsection
