@extends('pic.layouts.app')

@section('title', 'Catatan Kinerja Harian')
@section('page-title', 'Catatan Kinerja Harian')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        {{-- Header + tombol tambah --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                @if($todayLaporan)
                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Sudah isi hari ini</span>
                @else
                    <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-circle me-1"></i>Belum isi hari ini</span>
                @endif
            </div>
            <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#formCatatan">
                <i class="bi bi-pencil-square me-1"></i>
                {{ $todayLaporan ? 'Edit Catatan Hari Ini' : 'Isi Catatan Hari Ini' }}
            </button>
        </div>

        {{-- Form (collapse) --}}
        <div class="collapse {{ $errors->any() ? 'show' : '' }}" id="formCatatan">
            <div class="card shadow-sm mb-4 border-primary">
                <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-clipboard2-check me-2"></i><strong>Catatan Kinerja Harian</strong></span>
                    <span class="small opacity-75">
                        {{ \Carbon\Carbon::parse($today)->locale('id')->translatedFormat('l, d F Y') }}
                    </span>
                </div>
                <div class="card-body">

                    {{-- Panduan pengisian --}}
                    <div class="alert alert-info py-2 px-3 mb-3" style="font-size:0.82rem;">
                        <div class="fw-semibold mb-1"><i class="bi bi-info-circle me-1"></i>Panduan Pengisian</div>
                        <ul class="mb-0 ps-3">
                            <li>Catatan hanya bisa diisi atau diedit <strong>pada hari yang sama</strong>. Setelah hari berganti, catatan tidak bisa diubah.</li>
                            <li><strong>Catatan Kerja:</strong> tuliskan rencana / pekerjaan yang dilakukan hari ini.</li>
                            <li><strong>Laporan Kinerja:</strong> tuliskan realisasi atau hasil yang sudah dikerjakan.</li>
                            <li><strong>Bukti Hasil:</strong> upload file ke Google Drive lalu tempelkan link-nya (opsional).</li>
                            <li><strong>Capaian Hasil:</strong> geser slider untuk menunjukkan persentase penyelesaian pekerjaan hari ini.
                                <span class="badge bg-danger" style="font-size:0.65rem;">0–49%</span> Rendah &nbsp;
                                <span class="badge bg-warning text-dark" style="font-size:0.65rem;">50–79%</span> Cukup &nbsp;
                                <span class="badge bg-success" style="font-size:0.65rem;">80–100%</span> Baik
                            </li>
                        </ul>
                    </div>

                    <form action="{{ route('pic.laporan-harian.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="tanggal" value="{{ $today }}">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Catatan Kerja Hari Ini <span class="text-danger">*</span></label>
                            <textarea name="target_kerja" rows="3"
                                      class="form-control @error('target_kerja') is-invalid @enderror"
                                      placeholder="Contoh: Menyelesaikan review artikel jurnal A, koordinasi dengan editor, dll." required>{{ old('target_kerja', $todayLaporan->target_kerja ?? '') }}</textarea>
                            @error('target_kerja')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Laporan Kinerja <span class="text-danger">*</span></label>
                            <textarea name="laporan_kinerja" rows="3"
                                      class="form-control @error('laporan_kinerja') is-invalid @enderror"
                                      placeholder="Contoh: Sudah menyelesaikan review 2 artikel, mengirim feedback ke author." required>{{ old('laporan_kinerja', $todayLaporan->laporan_kinerja ?? '') }}</textarea>
                            @error('laporan_kinerja')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Bukti Hasil
                                <small class="text-muted fw-normal">(opsional — upload ke Google Drive)</small>
                            </label>
                            <input type="url" name="bukti_hasil"
                                   class="form-control @error('bukti_hasil') is-invalid @enderror"
                                   value="{{ old('bukti_hasil', $todayLaporan->bukti_hasil ?? '') }}"
                                   placeholder="https://drive.google.com/file/d/...">
                            @error('bukti_hasil')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Upload file ke Google Drive → klik kanan → <em>Bagikan</em> → salin link → tempelkan di sini.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                Capaian Hasil
                                <span class="badge ms-1" id="capaianBadge"
                                      style="font-size:0.8rem;min-width:52px;">
                                    {{ old('capaian_hasil', $todayLaporan->capaian_hasil ?? 0) }}%
                                </span>
                            </label>
                            <input type="range" name="capaian_hasil" id="capaianRange"
                                   class="form-range"
                                   min="0" max="100" step="5"
                                   value="{{ old('capaian_hasil', $todayLaporan->capaian_hasil ?? 0) }}">
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <span class="badge bg-danger" style="font-size:0.7rem;">0% — Belum mulai</span>
                                <span class="badge bg-warning text-dark" style="font-size:0.7rem;">50% — Separuh selesai</span>
                                <span class="badge bg-success" style="font-size:0.7rem;">100% — Selesai</span>
                            </div>
                            <div class="form-text mt-1"><i class="bi bi-hand-index me-1"></i>Geser slider sesuai persentase pekerjaan yang sudah terselesaikan hari ini.</div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="bi bi-save me-1"></i>
                                {{ $todayLaporan ? 'Update Catatan Hari Ini' : 'Simpan Catatan' }}
                            </button>
                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#formCatatan">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Riwayat --}}
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-2"></i><strong>Riwayat Catatan Saya</strong></span>
                <span class="badge bg-secondary">{{ $laporan->total() }} catatan</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:120px">Tanggal</th>
                            <th>Catatan Kerja</th>
                            <th>Laporan Kinerja</th>
                            <th style="width:90px" class="text-center">Capaian</th>
                            <th style="width:90px" class="text-center">Status</th>
                            <th style="width:60px" class="text-center">Bukti</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($laporan as $item)
                        <tr>
                            <td class="small text-nowrap">
                                {{ \Carbon\Carbon::parse($item->tanggal)->locale('id')->translatedFormat('d M Y') }}
                                @if($item->tanggal->isToday())
                                    <br><span class="badge bg-success">Hari ini</span>
                                @endif
                            </td>
                            <td class="small" style="max-width:200px">
                                <div title="{{ $item->target_kerja }}">{{ Str::limit($item->target_kerja, 80) }}</div>
                            </td>
                            <td class="small" style="max-width:200px">
                                <div title="{{ $item->laporan_kinerja }}">{{ Str::limit($item->laporan_kinerja, 80) }}</div>
                                @if($item->catatan_admin)
                                <div class="mt-1 p-1 bg-light rounded border-start border-info border-2 text-muted" style="font-size:0.75rem;">
                                    <i class="bi bi-chat-text text-info me-1"></i>{{ Str::limit($item->catatan_admin, 60) }}
                                </div>
                                @endif
                            </td>
                            <td class="text-center">
                                @php $c = $item->capaian_hasil; @endphp
                                <span class="badge {{ $c >= 80 ? 'bg-success' : ($c >= 50 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                    {{ $c }}%
                                </span>
                            </td>
                            <td class="text-center">
                                @if($item->validated_at)
                                    <span class="badge bg-success" title="Divalidasi {{ $item->validated_at->format('d/m/Y H:i') }}">
                                        <i class="bi bi-patch-check-fill me-1"></i>Valid
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-hourglass-split me-1"></i>Belum
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item->bukti_hasil)
                                <a href="{{ $item->bukti_hasil }}" target="_blank" class="btn btn-outline-info btn-sm" title="Lihat Bukti">
                                    <i class="bi bi-link-45deg"></i>
                                </a>
                                @else
                                <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-2"></i>Belum ada catatan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
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

// Buka form otomatis jika belum isi hari ini
@if(!$todayLaporan && !session('success'))
const collapseEl = document.getElementById('formCatatan');
if (collapseEl) {
    new bootstrap.Collapse(collapseEl, { show: true });
}
@endif
</script>
@endpush
