@extends('layouts.app')

@section('title', 'Detail Catatan Kinerja — ' . $pic->name)
@section('page-title', 'Detail Catatan Kinerja Harian')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="container-fluid">

    <div class="mb-3 d-flex align-items-center gap-2">
        <a href="{{ route('admin.laporan-harian.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
        <span class="text-muted small">
            {{ \Carbon\Carbon::parse($tanggal)->locale('id')->translatedFormat('l, d F Y') }}
            — <strong>{{ $pic->name }}</strong>
        </span>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-4">

        {{-- Daftar Kegiatan --}}
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span>
                        <i class="bi bi-list-task me-2"></i>
                        <strong>Kegiatan — {{ $pic->name }}</strong>
                    </span>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary">{{ $entries->count() }} kegiatan</span>
                        @if($allValidated)
                            <span class="badge bg-success"><i class="bi bi-patch-check-fill me-1"></i>Semua Valid</span>
                        @elseif($someValidated)
                            <span class="badge bg-warning text-dark"><i class="bi bi-patch-check me-1"></i>Sebagian Valid</span>
                        @else
                            <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Belum Divalidasi</span>
                        @endif
                    </div>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($entries as $i => $entry)
                    <div class="list-group-item px-4 py-3 {{ $entry->validated_at ? 'bg-success bg-opacity-10' : '' }}">
                        {{-- Header kegiatan --}}
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-secondary rounded-circle" style="width:24px;height:24px;display:flex;align-items:center;justify-content:center;font-size:0.7rem;">{{ $i+1 }}</span>
                                @if($entry->judul_kegiatan)
                                <span class="fw-semibold">{{ $entry->judul_kegiatan }}</span>
                                @else
                                <span class="text-muted small">Kegiatan {{ $i+1 }}</span>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                                @php $c = $entry->capaian_hasil; @endphp
                                <span class="badge {{ $c >= 80 ? 'bg-success' : ($c >= 50 ? 'bg-warning text-dark' : 'bg-danger') }}">{{ $c }}%</span>
                                @if($entry->bukti_hasil)
                                <a href="{{ $entry->bukti_hasil }}" target="_blank" class="badge bg-info text-white text-decoration-none">
                                    <i class="bi bi-link-45deg"></i> Bukti
                                </a>
                                @endif
                                @if($entry->validated_at)
                                    <span class="badge bg-success">
                                        <i class="bi bi-patch-check-fill me-1"></i>Valid
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-hourglass-split me-1"></i>Belum
                                    </span>
                                @endif
                            </div>
                        </div>
                        {{-- Isi kegiatan --}}
                        <div class="row g-2 small mb-3">
                            <div class="col-md-6">
                                <div class="text-muted fw-semibold mb-1">Catatan Kerja</div>
                                <div class="p-2 bg-light rounded" style="white-space:pre-wrap;">{{ $entry->target_kerja }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted fw-semibold mb-1">Laporan Kinerja</div>
                                <div class="p-2 bg-light rounded" style="white-space:pre-wrap;">{{ $entry->laporan_kinerja }}</div>
                            </div>
                        </div>

                        {{-- Form catatan + validasi --}}
                        <form action="{{ route('admin.laporan-harian.validate-entry', $entry) }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <textarea name="catatan_admin" rows="2"
                                          class="form-control form-control-sm"
                                          placeholder="Catatan / feedback (opsional)...">{{ $entry->catatan_admin }}</textarea>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                @if($entry->validated_at)
                                    <span class="text-success small">
                                        <i class="bi bi-patch-check-fill me-1"></i>Valid {{ $entry->validated_at->format('d/m H:i') }}
                                    </span>
                                    <button type="submit" name="action" value="save_catatan" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-save me-1"></i>Update Catatan
                                    </button>
                                    <button type="submit" name="action" value="unvalidate" class="btn btn-link btn-sm text-danger p-0"
                                            onclick="return confirm('Batalkan validasi?')">
                                        Batalkan validasi
                                    </button>
                                @else
                                    <button type="submit" name="action" value="validate" class="btn btn-success btn-sm px-4">
                                        <i class="bi bi-patch-check me-1"></i>Validasi
                                    </button>
                                    <span class="text-muted small">Belum divalidasi</span>
                                @endif
                            </div>
                        </form>
                    </div>
                    @empty
                    <div class="list-group-item text-center text-muted py-4">
                        <i class="bi bi-inbox fs-4 d-block mb-2"></i>Tidak ada data
                    </div>
                    @endforelse
                </div>

                {{-- Ringkasan capaian --}}
                @if($entries->count() > 1)
                <div class="card-footer bg-light">
                    <div class="d-flex align-items-center gap-3 small">
                        <span class="text-muted">Rata-rata Capaian:</span>
                        @php $avg = round($entries->avg('capaian_hasil')); @endphp
                        <span class="badge {{ $avg >= 80 ? 'bg-success' : ($avg >= 50 ? 'bg-warning text-dark' : 'bg-danger') }} fs-6">
                            {{ $avg }}%
                        </span>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Panel Ringkasan & Catatan --}}
        <div class="col-lg-4">
            {{-- Status ringkasan --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header">
                    <i class="bi bi-bar-chart-fill me-2"></i><strong>Ringkasan</strong>
                </div>
                <div class="card-body">
                    @php
                        $validatedCount = $entries->filter(fn($e) => $e->validated_at)->count();
                        $totalCount     = $entries->count();
                        $pct            = $totalCount > 0 ? round($validatedCount / $totalCount * 100) : 0;
                    @endphp
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted">Progress Validasi</span>
                        <span class="fw-semibold small">{{ $validatedCount }}/{{ $totalCount }}</span>
                    </div>
                    <div class="progress mb-3" style="height:8px;">
                        <div class="progress-bar {{ $pct === 100 ? 'bg-success' : 'bg-primary' }}"
                             style="width:{{ $pct }}%"></div>
                    </div>
                    @if($allValidated)
                        <div class="alert alert-success py-2 mb-0 small">
                            <i class="bi bi-patch-check-fill me-1"></i>Semua kegiatan sudah divalidasi
                        </div>
                    @elseif($someValidated)
                        <div class="alert alert-warning py-2 mb-0 small">
                            <i class="bi bi-patch-check me-1"></i>{{ $validatedCount }} dari {{ $totalCount }} kegiatan divalidasi
                        </div>
                    @else
                        <div class="alert alert-secondary py-2 mb-0 small">
                            <i class="bi bi-hourglass-split me-1"></i>Belum ada yang divalidasi
                        </div>
                    @endif
                </div>
            </div>

        </div>

    </div>

    {{-- Log Aktivitas --}}
    @if($logs->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-clock-history me-2"></i><strong>Log Aktivitas</strong></span>
                    <span class="badge bg-secondary">{{ $logs->count() }} entri</span>
                </div>
                <div class="card-body p-0">
                    @foreach($logs as $log)
                    <div class="d-flex gap-3 p-3 border-bottom align-items-start">
                        <div class="flex-shrink-0 pt-1">
                            <span class="badge bg-{{ $log->actionColor() }} rounded-circle p-2">
                                @if($log->action === 'created') <i class="bi bi-plus-lg"></i>
                                @elseif($log->action === 'updated') <i class="bi bi-pencil"></i>
                                @elseif($log->action === 'validated') <i class="bi bi-patch-check"></i>
                                @elseif($log->action === 'unvalidated') <i class="bi bi-x-circle"></i>
                                @else <i class="bi bi-chat-text"></i>
                                @endif
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="fw-semibold small">{{ $log->actor_name }}</span>
                                <span class="badge bg-{{ $log->actor_type === 'admin' ? 'dark' : 'primary' }}" style="font-size:0.65rem;">
                                    {{ $log->actor_type === 'admin' ? 'Admin' : 'PIC' }}
                                </span>
                                <span class="badge bg-{{ $log->actionColor() }}" style="font-size:0.65rem;">{{ $log->actionLabel() }}</span>
                            </div>
                            @if($log->changes)
                            <div class="mt-1">
                                @foreach($log->changes as $field => $diff)
                                <div class="small mb-1">
                                    <span class="text-muted fw-semibold">{{ \App\Models\LaporanHarianLog::fieldLabel($field) }}:</span>
                                    @if($field === 'capaian_hasil')
                                        <span class="badge bg-secondary">{{ $diff['old'] ?? '-' }}%</span>
                                        <i class="bi bi-arrow-right text-muted mx-1"></i>
                                        <span class="badge bg-primary">{{ $diff['new'] ?? '-' }}%</span>
                                    @else
                                        <span class="text-danger text-decoration-line-through">{{ Str::limit($diff['old'] ?? '-', 60) }}</span>
                                        <i class="bi bi-arrow-right text-muted mx-1"></i>
                                        <span class="text-success">{{ Str::limit($diff['new'] ?? '-', 60) }}</span>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        <div class="flex-shrink-0 text-muted small text-nowrap">
                            {{ $log->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
