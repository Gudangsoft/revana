@extends('pic.layouts.app')

@section('title', 'Edit Catatan Kegiatan')
@section('page-title', 'Edit Catatan Kegiatan')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="mb-3">
            <a href="{{ route('pic.laporan-harian.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
        </div>

        <div class="card shadow-sm border-primary">
            <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                <span><i class="bi bi-pencil-square me-2"></i><strong>Edit Catatan Kegiatan</strong></span>
                <span class="small opacity-75">
                    {{ $laporanHarian->tanggal->locale('id')->translatedFormat('l, d F Y') }}
                </span>
            </div>
            <div class="card-body">
                <form action="{{ route('pic.laporan-harian.update', $laporanHarian) }}" method="POST">
                    @csrf @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Judul Kegiatan <small class="text-muted fw-normal">(opsional)</small></label>
                        <input type="text" name="judul_kegiatan"
                               class="form-control @error('judul_kegiatan') is-invalid @enderror"
                               value="{{ old('judul_kegiatan', $laporanHarian->judul_kegiatan) }}"
                               placeholder="Cth: Review artikel jurnal, Koordinasi editor, dll.">
                        @error('judul_kegiatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Catatan Kerja <span class="text-danger">*</span></label>
                            <textarea name="target_kerja" rows="4"
                                      class="form-control @error('target_kerja') is-invalid @enderror"
                                      required>{{ old('target_kerja', $laporanHarian->target_kerja) }}</textarea>
                            @error('target_kerja')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Laporan Kinerja <span class="text-danger">*</span></label>
                            <textarea name="laporan_kinerja" rows="4"
                                      class="form-control @error('laporan_kinerja') is-invalid @enderror"
                                      required>{{ old('laporan_kinerja', $laporanHarian->laporan_kinerja) }}</textarea>
                            @error('laporan_kinerja')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Bukti Hasil <small class="text-muted fw-normal">(opsional)</small></label>
                            <input type="url" name="bukti_hasil"
                                   class="form-control @error('bukti_hasil') is-invalid @enderror"
                                   value="{{ old('bukti_hasil', $laporanHarian->bukti_hasil) }}"
                                   placeholder="https://drive.google.com/...">
                            @error('bukti_hasil')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Capaian Hasil
                                <span class="badge ms-1" id="capaianBadge" style="font-size:0.8rem;min-width:52px;">
                                    {{ old('capaian_hasil', $laporanHarian->capaian_hasil) }}%
                                </span>
                            </label>
                            <input type="range" name="capaian_hasil" id="capaianRange"
                                   class="form-range" min="0" max="100" step="5"
                                   value="{{ old('capaian_hasil', $laporanHarian->capaian_hasil) }}">
                            <div class="d-flex justify-content-between mt-1">
                                <span class="badge bg-danger" style="font-size:0.65rem;">0% Belum</span>
                                <span class="badge bg-warning text-dark" style="font-size:0.65rem;">50% Separuh</span>
                                <span class="badge bg-success" style="font-size:0.65rem;">100% Selesai</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Simpan Perubahan
                        </button>
                        <a href="{{ route('pic.laporan-harian.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
const range = document.getElementById('capaianRange');
const badge = document.getElementById('capaianBadge');
if (range && badge) {
    const update = (v) => {
        badge.textContent = v + '%';
        badge.className = 'badge ms-1 ' + (v >= 80 ? 'bg-success' : v >= 50 ? 'bg-warning text-dark' : 'bg-danger');
    };
    update(parseInt(range.value));
    range.addEventListener('input', function() { update(parseInt(this.value)); });
}
</script>
@endpush
