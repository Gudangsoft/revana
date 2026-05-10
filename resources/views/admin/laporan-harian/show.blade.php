@extends('layouts.app')

@section('title', 'Detail Catatan Kinerja Harian')
@section('page-title', 'Detail Catatan Kinerja Harian')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="container-fluid">

    <div class="mb-3">
        <a href="{{ route('admin.laporan-harian.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-4">

        {{-- Detail Catatan --}}
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-clipboard2-check me-2"></i><strong>Detail Catatan Kinerja</strong></span>
                    @if($laporanHarian->is_validated)
                        <span class="badge bg-success"><i class="bi bi-patch-check-fill me-1"></i>Sudah Divalidasi</span>
                    @else
                        <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Belum Divalidasi</span>
                    @endif
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        @if($laporanHarian->judul_kegiatan)
                        <tr>
                            <th style="width:160px" class="text-muted fw-normal small">Judul Kegiatan</th>
                            <td class="fw-semibold">{{ $laporanHarian->judul_kegiatan }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th style="width:160px" class="text-muted fw-normal small">Tanggal</th>
                            <td class="fw-semibold">
                                {{ $laporanHarian->tanggal->locale('id')->translatedFormat('l, d F Y') }}
                                @if($laporanHarian->tanggal->isToday())
                                    <span class="badge bg-success ms-1">Hari ini</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-normal small">PIC</th>
                            <td class="fw-semibold">{{ $laporanHarian->pic->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-normal small">Capaian Hasil</th>
                            <td>
                                @php $c = $laporanHarian->capaian_hasil; @endphp
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:10px;max-width:200px">
                                        <div class="progress-bar {{ $c >= 80 ? 'bg-success' : ($c >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                             style="width:{{ $c }}%"></div>
                                    </div>
                                    <span class="badge {{ $c >= 80 ? 'bg-success' : ($c >= 50 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                        {{ $c }}%
                                    </span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-normal small">Dibuat</th>
                            <td class="small text-muted">{{ $laporanHarian->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>

                    <hr>

                    <div class="mb-3">
                        <div class="small text-muted fw-semibold mb-1"><i class="bi bi-pencil-square me-1"></i>Catatan Kerja Hari Ini</div>
                        <div class="p-3 bg-light rounded" style="white-space:pre-wrap;font-size:0.9rem;">{{ $laporanHarian->target_kerja ?? '-' }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-muted fw-semibold mb-1"><i class="bi bi-check2-square me-1"></i>Laporan Kinerja / Realisasi</div>
                        <div class="p-3 bg-light rounded" style="white-space:pre-wrap;font-size:0.9rem;">{{ $laporanHarian->laporan_kinerja ?? '-' }}</div>
                    </div>

                    @if($laporanHarian->bukti_hasil)
                    <div>
                        <div class="small text-muted fw-semibold mb-1"><i class="bi bi-link-45deg me-1"></i>Bukti Hasil</div>
                        <a href="{{ $laporanHarian->bukti_hasil }}" target="_blank" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-box-arrow-up-right me-1"></i>Buka Link Bukti
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Validasi Admin --}}
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header">
                    <i class="bi bi-shield-check me-2"></i><strong>Validasi Admin</strong>
                </div>
                <div class="card-body">

                    @if($laporanHarian->is_validated)
                    <div class="alert alert-success d-flex align-items-start gap-2 mb-3">
                        <i class="bi bi-patch-check-fill fs-5 mt-1 flex-shrink-0"></i>
                        <div>
                            <div class="fw-semibold">Sudah Divalidasi</div>
                            <div class="small">
                                Oleh: <strong>{{ $laporanHarian->validator->name ?? 'Admin' }}</strong><br>
                                Pada: {{ $laporanHarian->validated_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-warning d-flex align-items-start gap-2 mb-3">
                        <i class="bi bi-hourglass-split fs-5 mt-1 flex-shrink-0"></i>
                        <div class="fw-semibold">Belum Divalidasi</div>
                    </div>
                    @endif

                    <form action="{{ route('admin.laporan-harian.validate', $laporanHarian) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Catatan Admin <small class="text-muted">(opsional)</small></label>
                            <textarea name="catatan_admin" rows="4"
                                      class="form-control form-control-sm @error('catatan_admin') is-invalid @enderror"
                                      placeholder="Tuliskan catatan atau feedback untuk PIC...">{{ old('catatan_admin', $laporanHarian->catatan_admin) }}</textarea>
                            @error('catatan_admin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-flex gap-2">
                            @if(!$laporanHarian->is_validated)
                            <button type="submit" name="action" value="validate" class="btn btn-success flex-fill">
                                <i class="bi bi-patch-check me-1"></i>Validasi
                            </button>
                            @else
                            <button type="submit" name="action" value="validate" class="btn btn-outline-success flex-fill">
                                <i class="bi bi-arrow-clockwise me-1"></i>Update Catatan
                            </button>
                            <button type="submit" name="action" value="unvalidate" class="btn btn-outline-danger"
                                    onclick="return confirm('Batalkan validasi catatan ini?')">
                                <i class="bi bi-x-circle me-1"></i>Batal Validasi
                            </button>
                            @endif
                        </div>
                    </form>

                </div>
            </div>

            @if($laporanHarian->catatan_admin)
            <div class="card shadow-sm mt-3">
                <div class="card-header">
                    <i class="bi bi-chat-text me-2"></i><strong>Catatan Admin Tersimpan</strong>
                </div>
                <div class="card-body">
                    <div class="p-2 bg-light rounded small" style="white-space:pre-wrap;">{{ $laporanHarian->catatan_admin }}</div>
                </div>
            </div>
            @endif
        </div>

    </div>

    {{-- Log Aktivitas --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-clock-history me-2"></i><strong>Log Aktivitas</strong></span>
                    <span class="badge bg-secondary">{{ $logs->count() }} entri</span>
                </div>
                <div class="card-body p-0">
                    @forelse($logs as $log)
                    <div class="d-flex gap-3 p-3 border-bottom align-items-start">
                        {{-- Icon --}}
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
                        {{-- Content --}}
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
                        {{-- Time --}}
                        <div class="flex-shrink-0 text-muted small text-nowrap">
                            {{ $log->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    @empty
                    <div class="p-4 text-center text-muted small">
                        <i class="bi bi-inbox fs-4 d-block mb-2"></i>Belum ada log aktivitas
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
