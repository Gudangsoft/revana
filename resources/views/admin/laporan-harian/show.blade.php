@extends('layouts.app')

@section('title', 'Detail Catatan Kinerja — ' . $pic->name)
@section('page-title', 'Detail Catatan Kinerja Harian')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
@php
    $tanggalCarbon   = \Carbon\Carbon::parse($tanggal);
    $validatedCount  = $entries->filter(fn($e) => $e->validated_at)->count();
    $totalCount      = $entries->count();
    $pct             = $totalCount > 0 ? round($validatedCount / $totalCount * 100) : 0;
    $avgCapaian      = $totalCount > 0 ? round($entries->avg('capaian_hasil')) : 0;
@endphp

{{-- Hero Header --}}
<div class="rounded-3 mb-4 p-4 text-white shadow-sm"
     style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <a href="{{ route('admin.laporan-harian.index') }}"
               class="btn btn-sm btn-light btn-outline-white opacity-75 mb-2 d-inline-flex align-items-center gap-1"
               style="color:#4f46e5;">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <h4 class="mb-1 fw-bold">{{ $pic->name }}</h4>
            <div class="opacity-75 small">
                <i class="bi bi-calendar3 me-1"></i>
                {{ $tanggalCarbon->locale('id')->translatedFormat('l, d F Y') }}
            </div>
        </div>
        <div class="d-flex flex-wrap gap-3 text-center">
            <div class="bg-white bg-opacity-15 rounded-3 px-4 py-2">
                <div class="fs-4 fw-bold">{{ $totalCount }}</div>
                <div class="small opacity-75">Kegiatan</div>
            </div>
            <div class="bg-white bg-opacity-15 rounded-3 px-4 py-2">
                <div class="fs-4 fw-bold">{{ $avgCapaian }}%</div>
                <div class="small opacity-75">Rata-rata</div>
            </div>
            <div class="bg-white bg-opacity-15 rounded-3 px-4 py-2">
                <div class="fs-4 fw-bold">{{ $validatedCount }}/{{ $totalCount }}</div>
                <div class="small opacity-75">Tervalidasi</div>
            </div>
        </div>
    </div>
    {{-- Progress validasi --}}
    <div class="mt-3">
        <div class="d-flex justify-content-between small opacity-75 mb-1">
            <span>Progress Validasi</span>
            <span>{{ $pct }}%</span>
        </div>
        <div class="progress" style="height:6px;background:rgba(255,255,255,0.2);">
            <div class="progress-bar bg-white" style="width:{{ $pct }}%; transition:width 0.6s ease;"></div>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show shadow-sm">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">

    {{-- Kolom Kiri: Daftar Kegiatan --}}
    <div class="col-lg-8">

        @forelse($entries as $i => $entry)
        @php
            $c          = $entry->capaian_hasil;
            $isValid    = (bool) $entry->validated_at;
            $colorClass = $c >= 80 ? 'success' : ($c >= 50 ? 'warning' : 'danger');
        @endphp

        <div class="card shadow-sm mb-3 border-0 {{ $isValid ? 'border-start border-success border-3' : 'border-start border-secondary border-3' }}"
             style="border-left-width:4px!important;">
            {{-- Card header --}}
            <div class="card-header bg-transparent d-flex align-items-center justify-content-between py-2 px-4"
                 style="border-bottom: 1px solid rgba(0,0,0,0.06);">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-circle fw-bold text-white"
                         style="width:32px;height:32px;font-size:0.8rem;flex-shrink:0;
                                background:{{ $isValid ? '#10b981' : '#94a3b8' }};">
                        {{ $i + 1 }}
                    </div>
                    <div>
                        @if($entry->judul_kegiatan)
                        <div class="fw-semibold">{{ $entry->judul_kegiatan }}</div>
                        @else
                        <div class="text-muted small">Kegiatan {{ $i + 1 }}</div>
                        @endif
                        <div class="d-flex align-items-center gap-2 mt-1">
                            {{-- Capaian badge + mini bar --}}
                            <span class="badge bg-{{ $colorClass }}{{ $colorClass === 'warning' ? ' text-dark' : '' }}">{{ $c }}%</span>
                            <div class="progress" style="width:80px;height:5px;">
                                <div class="progress-bar bg-{{ $colorClass }}" style="width:{{ $c }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if($entry->bukti_hasil)
                    <a href="{{ $entry->bukti_hasil }}" target="_blank"
                       class="btn btn-outline-info btn-sm d-flex align-items-center gap-1" style="font-size:0.78rem;">
                        <i class="bi bi-box-arrow-up-right"></i> Bukti
                    </a>
                    @endif
                    @if($isValid)
                        <span class="badge bg-success d-flex align-items-center gap-1">
                            <i class="bi bi-patch-check-fill"></i> Valid
                        </span>
                    @else
                        <span class="badge bg-secondary d-flex align-items-center gap-1">
                            <i class="bi bi-hourglass-split"></i> Belum
                        </span>
                    @endif
                </div>
            </div>

            {{-- Card body: isi kegiatan --}}
            <div class="card-body px-4 py-3">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="small fw-semibold text-muted mb-1">
                            <i class="bi bi-pencil-square me-1"></i>Catatan Kerja
                        </div>
                        <div class="p-3 rounded small"
                             style="background:#f8fafc;border:1px solid #e2e8f0;white-space:pre-wrap;line-height:1.6;">{{ $entry->target_kerja }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small fw-semibold text-muted mb-1">
                            <i class="bi bi-clipboard2-check me-1"></i>Laporan Kinerja
                        </div>
                        <div class="p-3 rounded small"
                             style="background:#f8fafc;border:1px solid #e2e8f0;white-space:pre-wrap;line-height:1.6;">{{ $entry->laporan_kinerja }}</div>
                    </div>
                </div>

                {{-- Form validasi --}}
                <form action="{{ route('admin.laporan-harian.validate-entry', $entry) }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="small fw-semibold text-muted mb-1 d-block">
                            <i class="bi bi-chat-text me-1"></i>Catatan / Feedback Admin
                        </label>
                        <textarea name="catatan_admin" rows="2"
                                  class="form-control form-control-sm"
                                  placeholder="Tulis catatan atau feedback untuk PIC (opsional)...">{{ $entry->catatan_admin }}</textarea>
                    </div>

                    @if($isValid)
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2 text-success small">
                            <i class="bi bi-patch-check-fill"></i>
                            <span>Divalidasi {{ $entry->validated_at->format('d/m/Y H:i') }}</span>
                            @if($entry->validator)
                            <span class="text-muted">
                                · <i class="bi bi-person-fill me-1"></i>{{ $entry->validator->name }}
                            </span>
                            @endif
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" name="action" value="save_catatan"
                                    class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-save me-1"></i>Update Catatan
                            </button>
                            <button type="submit" name="action" value="unvalidate"
                                    class="btn btn-outline-danger btn-sm"
                                    onclick="return confirm('Batalkan validasi kegiatan ini?')">
                                <i class="bi bi-x-circle me-1"></i>Batalkan
                            </button>
                        </div>
                    </div>
                    @else
                    <div class="d-flex align-items-center gap-2">
                        <button type="submit" name="action" value="validate"
                                class="btn btn-success btn-sm px-4">
                            <i class="bi bi-patch-check me-1"></i>Validasi Kegiatan
                        </button>
                        <span class="small text-muted">Belum divalidasi</span>
                    </div>
                    @endif
                </form>
            </div>
        </div>
        @empty
        <div class="card shadow-sm border-0 text-center py-5 text-muted">
            <i class="bi bi-inbox fs-2 d-block mb-2"></i>Tidak ada catatan kegiatan
        </div>
        @endforelse

        {{-- Footer rata-rata jika > 1 entry --}}
        @if($totalCount > 1)
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-4 py-2 px-4">
                <span class="small text-muted fw-semibold">Rata-rata Capaian:</span>
                <span class="badge {{ $avgCapaian >= 80 ? 'bg-success' : ($avgCapaian >= 50 ? 'bg-warning text-dark' : 'bg-danger') }} fs-6">
                    {{ $avgCapaian }}%
                </span>
                <div class="progress flex-grow-1" style="height:8px;">
                    <div class="progress-bar {{ $avgCapaian >= 80 ? 'bg-success' : ($avgCapaian >= 50 ? 'bg-warning' : 'bg-danger') }}"
                         style="width:{{ $avgCapaian }}%"></div>
                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- Kolom Kanan: Info Panel --}}
    <div class="col-lg-4">

        {{-- Info PIC --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold"
                         style="width:48px;height:48px;font-size:1.1rem;flex-shrink:0;">
                        {{ strtoupper(substr($pic->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-bold">{{ $pic->name }}</div>
                        <div class="small text-muted">{{ $tanggalCarbon->locale('id')->translatedFormat('d F Y') }}</div>
                    </div>
                </div>
                {{-- Status badge --}}
                @if($allValidated)
                <div class="rounded-3 text-center py-2 px-3"
                     style="background:linear-gradient(135deg,#d1fae5,#a7f3d0);">
                    <i class="bi bi-patch-check-fill text-success me-1"></i>
                    <span class="small fw-semibold text-success">Semua Kegiatan Tervalidasi</span>
                </div>
                @elseif($someValidated)
                <div class="rounded-3 text-center py-2 px-3"
                     style="background:linear-gradient(135deg,#fef9c3,#fde68a);">
                    <i class="bi bi-patch-check text-warning me-1"></i>
                    <span class="small fw-semibold text-warning">{{ $validatedCount }}/{{ $totalCount }} Tervalidasi</span>
                </div>
                @else
                <div class="rounded-3 text-center py-2 px-3"
                     style="background:linear-gradient(135deg,#f1f5f9,#e2e8f0);">
                    <i class="bi bi-hourglass-split text-secondary me-1"></i>
                    <span class="small fw-semibold text-secondary">Belum Ada Validasi</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Progress Validasi --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent border-bottom-0 pb-0">
                <span class="fw-semibold small"><i class="bi bi-bar-chart-fill me-2 text-primary"></i>Progress Validasi</span>
            </div>
            <div class="card-body pt-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small text-muted">{{ $validatedCount }} dari {{ $totalCount }} kegiatan</span>
                    <span class="fw-bold {{ $pct === 100 ? 'text-success' : 'text-primary' }}">{{ $pct }}%</span>
                </div>
                <div class="progress mb-3" style="height:10px;border-radius:99px;">
                    <div class="progress-bar {{ $pct === 100 ? 'bg-success' : 'bg-primary' }}"
                         style="width:{{ $pct }}%;transition:width 0.6s ease;border-radius:99px;"></div>
                </div>
                {{-- Capaian per kegiatan --}}
                @foreach($entries as $e)
                <div class="d-flex align-items-center gap-2 mb-1">
                    @php $ec = $e->capaian_hasil; @endphp
                    <span class="small text-muted" style="min-width:16px;">{{ $loop->iteration }}</span>
                    <div class="progress flex-grow-1" style="height:6px;">
                        <div class="progress-bar bg-{{ $ec >= 80 ? 'success' : ($ec >= 50 ? 'warning' : 'danger') }}"
                             style="width:{{ $ec }}%"></div>
                    </div>
                    <span class="small text-muted" style="min-width:32px;">{{ $ec }}%</span>
                    @if($e->validated_at)
                        <i class="bi bi-patch-check-fill text-success small"></i>
                    @else
                        <i class="bi bi-circle text-muted small"></i>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- Navigasi hari --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center gap-2">
                <a href="{{ route('admin.laporan-harian.show', [$pic->id, $tanggalCarbon->copy()->subDay()->toDateString()]) }}"
                   class="btn btn-outline-secondary btn-sm flex-fill">
                    <i class="bi bi-chevron-left me-1"></i>Hari Sebelumnya
                </a>
                <a href="{{ route('admin.laporan-harian.show', [$pic->id, $tanggalCarbon->copy()->addDay()->toDateString()]) }}"
                   class="btn btn-outline-secondary btn-sm flex-fill">
                    Hari Berikutnya<i class="bi bi-chevron-right ms-1"></i>
                </a>
            </div>
        </div>

    </div>
</div>

{{-- Log Aktivitas --}}
@if($logs->count() > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                <span class="fw-semibold"><i class="bi bi-clock-history me-2 text-muted"></i>Log Aktivitas</span>
                <span class="badge bg-secondary">{{ $logs->count() }} entri</span>
            </div>
            <div class="card-body p-0">
                {{-- Timeline --}}
                <div class="px-4 py-3" style="position:relative;">
                    {{-- Garis vertikal --}}
                    <div style="position:absolute;left:44px;top:16px;bottom:16px;width:2px;background:#e2e8f0;z-index:0;"></div>

                    @foreach($logs as $log)
                    @php
                        $iconMap = [
                            'created'     => 'bi-plus-lg',
                            'updated'     => 'bi-pencil',
                            'validated'   => 'bi-patch-check-fill',
                            'unvalidated' => 'bi-x-circle-fill',
                            'catatan'     => 'bi-chat-text-fill',
                        ];
                        $icon = $iconMap[$log->action] ?? 'bi-circle-fill';
                    @endphp
                    <div class="d-flex gap-3 mb-4" style="position:relative;z-index:1;">
                        {{-- Icon dot --}}
                        <div class="d-flex align-items-center justify-content-center rounded-circle text-white flex-shrink-0"
                             style="width:32px;height:32px;background:var(--bs-{{ $log->actionColor() }});">
                            <i class="bi {{ $icon }}" style="font-size:0.75rem;"></i>
                        </div>
                        {{-- Content --}}
                        <div class="flex-grow-1 pt-1">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="fw-semibold small">{{ $log->actor_name }}</span>
                                <span class="badge bg-{{ $log->actor_type === 'admin' ? 'dark' : 'primary' }}"
                                      style="font-size:0.62rem;">
                                    {{ $log->actor_type === 'admin' ? 'Admin' : 'PIC' }}
                                </span>
                                <span class="badge bg-{{ $log->actionColor() }}" style="font-size:0.62rem;">
                                    {{ $log->actionLabel() }}
                                </span>
                                <span class="text-muted" style="font-size:0.72rem;">
                                    {{ $log->created_at->format('d/m/Y H:i') }}
                                </span>
                            </div>
                            @if($log->changes)
                            <div class="mt-1 p-2 rounded small" style="background:#f8fafc;border:1px solid #e2e8f0;">
                                @foreach($log->changes as $field => $diff)
                                <div class="{{ !$loop->first ? 'mt-1 pt-1 border-top' : '' }}">
                                    <span class="text-muted fw-semibold">{{ \App\Models\LaporanHarianLog::fieldLabel($field) }}:</span>
                                    @if($field === 'capaian_hasil')
                                        <span class="badge bg-secondary ms-1">{{ $diff['old'] ?? '-' }}%</span>
                                        <i class="bi bi-arrow-right text-muted mx-1"></i>
                                        <span class="badge bg-primary">{{ $diff['new'] ?? '-' }}%</span>
                                    @else
                                        <span class="text-danger text-decoration-line-through ms-1">{{ Str::limit($diff['old'] ?? '-', 50) }}</span>
                                        <i class="bi bi-arrow-right text-muted mx-1"></i>
                                        <span class="text-success">{{ Str::limit($diff['new'] ?? '-', 50) }}</span>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection
