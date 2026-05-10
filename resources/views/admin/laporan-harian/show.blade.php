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
</div>
@endsection
