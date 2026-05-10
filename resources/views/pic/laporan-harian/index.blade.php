@extends('pic.layouts.app')

@section('title', 'Catatan Kinerja Harian')
@section('page-title', 'Catatan Kinerja Harian')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-lg-7">

        {{-- Form Input --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-clipboard2-check me-2"></i>
                <strong>Catatan Kinerja Harian</strong>
            </div>
            <div class="card-body">

                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <form action="{{ route('pic.laporan-harian.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal"
                               class="form-control @error('tanggal') is-invalid @enderror"
                               value="{{ old('tanggal', $today) }}" max="{{ $today }}" required>
                        @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Catatan Kerja Hari Ini <span class="text-danger">*</span></label>
                        <textarea name="target_kerja" rows="4"
                                  class="form-control @error('target_kerja') is-invalid @enderror"
                                  placeholder="Tuliskan catatan pekerjaan hari ini..." required>{{ old('target_kerja', $todayLaporan->target_kerja ?? '') }}</textarea>
                        @error('target_kerja')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Laporan Kinerja <span class="text-danger">*</span></label>
                        <textarea name="laporan_kinerja" rows="4"
                                  class="form-control @error('laporan_kinerja') is-invalid @enderror"
                                  placeholder="Tuliskan realisasi pekerjaan yang sudah dikerjakan..." required>{{ old('laporan_kinerja', $todayLaporan->laporan_kinerja ?? '') }}</textarea>
                        @error('laporan_kinerja')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Bukti Hasil <small class="text-muted">(Upload ke Google Drive)</small></label>
                        <input type="url" name="bukti_hasil"
                               class="form-control @error('bukti_hasil') is-invalid @enderror"
                               value="{{ old('bukti_hasil', $todayLaporan->bukti_hasil ?? '') }}"
                               placeholder="Cth: https://drive.google.com/file/d/...">
                        @error('bukti_hasil')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">Upload file ke Google Drive lalu tempelkan link-nya di sini.</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            Capaian Hasil %
                            <span class="badge bg-primary ms-1" id="capaianBadge">{{ old('capaian_hasil', $todayLaporan->capaian_hasil ?? 0) }}%</span>
                        </label>
                        <input type="range" name="capaian_hasil" id="capaianRange"
                               class="form-range @error('capaian_hasil') is-invalid @enderror"
                               min="0" max="100" step="5"
                               value="{{ old('capaian_hasil', $todayLaporan->capaian_hasil ?? 0) }}">
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">0%</small>
                            <small class="text-muted">50%</small>
                            <small class="text-muted">100%</small>
                        </div>
                        @error('capaian_hasil')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>
                            {{ $todayLaporan ? 'Update Catatan Hari Ini' : 'Simpan Catatan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        {{-- Riwayat Laporan --}}
        <div class="card shadow-sm">
            <div class="card-header">
                <i class="bi bi-clock-history me-2"></i><strong>Riwayat Catatan Saya</strong>
            </div>
            <div class="card-body p-0">
                @forelse($laporan as $item)
                <div class="p-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <span class="fw-semibold text-primary small">
                            {{ \Carbon\Carbon::parse($item->tanggal)->locale('id')->translatedFormat('d F Y') }}
                        </span>
                        <span class="badge {{ $item->capaian_hasil >= 80 ? 'bg-success' : ($item->capaian_hasil >= 50 ? 'bg-warning text-dark' : 'bg-danger') }}">
                            {{ $item->capaian_hasil }}%
                        </span>
                    </div>
                    @if($item->target_kerja)
                    <div class="small text-muted mb-1"><strong>Catatan:</strong> {{ Str::limit($item->target_kerja, 80) }}</div>
                    @endif
                    @if($item->laporan_kinerja)
                    <div class="small text-muted mb-1"><strong>Realisasi:</strong> {{ Str::limit($item->laporan_kinerja, 80) }}</div>
                    @endif
                    @if($item->bukti_hasil)
                    <a href="{{ $item->bukti_hasil }}" target="_blank" class="small text-decoration-none">
                        <i class="bi bi-link-45deg"></i> Bukti Hasil
                    </a>
                    @endif
                </div>
                @empty
                <div class="p-4 text-center text-muted small">
                    <i class="bi bi-inbox fs-4 d-block mb-2"></i>Belum ada laporan
                </div>
                @endforelse
            </div>
            @if($laporan->hasPages())
            <div class="card-footer">
                {{ $laporan->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const range = document.getElementById('capaianRange');
const badge = document.getElementById('capaianBadge');
if (range && badge) {
    range.addEventListener('input', function() {
        badge.textContent = this.value + '%';
        const v = parseInt(this.value);
        badge.className = 'badge ms-1 ' + (v >= 80 ? 'bg-success' : v >= 50 ? 'bg-warning text-dark' : 'bg-danger');
    });
}
</script>
@endpush
