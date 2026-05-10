@extends('pic.layouts.app')

@section('title', 'Catatan Kinerja Harian')
@section('page-title', 'Catatan Kinerja Harian')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">

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

        {{-- Pengingat otomatis --}}
        @if($showReminder)
        <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-3 shadow-sm" role="alert">
            <i class="bi bi-alarm-fill fs-3 text-warning flex-shrink-0"></i>
            <div class="flex-grow-1">
                <strong>Pengingat!</strong> Anda belum mengisi catatan kinerja hari ini.
                <div class="small mt-1">Jangan lupa catat kegiatan Anda sebelum hari berakhir.</div>
            </div>
            <button type="button" class="btn btn-warning btn-sm"
                    onclick="document.getElementById('formTambah').classList.add('show');this.closest('.alert').remove();">
                <i class="bi bi-plus-circle me-1"></i>Isi Sekarang
            </button>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        {{-- Header hari ini --}}
        <div class="card shadow-sm mb-3 border-0 bg-primary text-white">
            <div class="card-body d-flex align-items-center justify-content-between py-3">
                <div>
                    <div class="fw-bold fs-5">
                        <i class="bi bi-calendar-check me-2"></i>
                        {{ \Carbon\Carbon::parse($today)->locale('id')->translatedFormat('l, d F Y') }}
                    </div>
                    <div class="small opacity-75 mt-1">
                        {{ $todayEntries->count() }} kegiatan tercatat hari ini
                    </div>
                </div>
                <button class="btn btn-light btn-sm fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#formTambah">
                    <i class="bi bi-plus-circle me-1"></i>Tambah Kegiatan
                </button>
            </div>
        </div>

        {{-- Form tambah (collapse) --}}
        <div class="collapse {{ $errors->any() ? 'show' : '' }}" id="formTambah">
            <div class="card shadow-sm mb-3 border-primary">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-plus-circle me-2"></i><strong>Tambah Catatan Kegiatan</strong>
                </div>
                <div class="card-body">

                    <div class="alert alert-info py-2 px-3 mb-3" style="font-size:0.82rem;">
                        <ul class="mb-0 ps-3">
                            <li>Catatan hanya bisa ditambahkan dan diedit <strong>pada hari yang sama</strong>.</li>
                            <li>Anda bisa menambahkan <strong>beberapa catatan</strong> untuk kegiatan yang berbeda dalam satu hari.</li>
                            <li><strong>Capaian Hasil:</strong> <span class="badge bg-danger" style="font-size:0.65rem;">0–49%</span> Rendah &nbsp;<span class="badge bg-warning text-dark" style="font-size:0.65rem;">50–79%</span> Cukup &nbsp;<span class="badge bg-success" style="font-size:0.65rem;">80–100%</span> Baik</li>
                        </ul>
                    </div>

                    <form action="{{ route('pic.laporan-harian.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="tanggal" value="{{ $today }}">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Judul Kegiatan <small class="text-muted fw-normal">(opsional)</small></label>
                            <input type="text" name="judul_kegiatan"
                                   class="form-control @error('judul_kegiatan') is-invalid @enderror"
                                   value="{{ old('judul_kegiatan') }}"
                                   placeholder="Cth: Review artikel jurnal, Koordinasi editor, dll.">
                            @error('judul_kegiatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Catatan Kerja <span class="text-danger">*</span></label>
                                <textarea name="target_kerja" rows="3"
                                          class="form-control @error('target_kerja') is-invalid @enderror"
                                          placeholder="Uraikan kegiatan yang dilakukan..." required>{{ old('target_kerja') }}</textarea>
                                @error('target_kerja')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Laporan Kinerja <span class="text-danger">*</span></label>
                                <textarea name="laporan_kinerja" rows="3"
                                          class="form-control @error('laporan_kinerja') is-invalid @enderror"
                                          placeholder="Uraikan hasil / realisasi kegiatan..." required>{{ old('laporan_kinerja') }}</textarea>
                                @error('laporan_kinerja')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Bukti Hasil <small class="text-muted fw-normal">(opsional)</small></label>
                                <input type="url" name="bukti_hasil"
                                       class="form-control @error('bukti_hasil') is-invalid @enderror"
                                       value="{{ old('bukti_hasil') }}"
                                       placeholder="https://drive.google.com/...">
                                @error('bukti_hasil')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="form-text">Upload ke Google Drive → salin link → tempel di sini.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Capaian Hasil
                                    <span class="badge ms-1" id="capaianBadge" style="font-size:0.8rem;min-width:52px;">{{ old('capaian_hasil', 0) }}%</span>
                                </label>
                                <input type="range" name="capaian_hasil" id="capaianRange"
                                       class="form-range" min="0" max="100" step="5"
                                       value="{{ old('capaian_hasil', 0) }}">
                                <div class="d-flex justify-content-between mt-1">
                                    <span class="badge bg-danger" style="font-size:0.65rem;">0% Belum</span>
                                    <span class="badge bg-warning text-dark" style="font-size:0.65rem;">50% Separuh</span>
                                    <span class="badge bg-success" style="font-size:0.65rem;">100% Selesai</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i>Tambah Catatan
                            </button>
                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#formTambah">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Catatan hari ini --}}
        @if($todayEntries->count() > 0)
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <i class="bi bi-sun me-2 text-warning"></i><strong>Catatan Hari Ini</strong>
                <span class="badge bg-primary ms-2">{{ $todayEntries->count() }}</span>
            </div>
            <div class="list-group list-group-flush">
                @foreach($todayEntries as $item)
                <div class="list-group-item px-3 py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1 me-3">
                            @if($item->judul_kegiatan)
                            <div class="fw-semibold mb-1">{{ $item->judul_kegiatan }}</div>
                            @endif
                            <div class="small text-muted mb-1">
                                <span class="fw-semibold text-dark">Catatan:</span> {{ Str::limit($item->target_kerja, 120) }}
                            </div>
                            <div class="small text-muted mb-1">
                                <span class="fw-semibold text-dark">Realisasi:</span> {{ Str::limit($item->laporan_kinerja, 120) }}
                            </div>
                            <div class="d-flex align-items-center gap-2 mt-2">
                                @php $c = $item->capaian_hasil; @endphp
                                <span class="badge {{ $c >= 80 ? 'bg-success' : ($c >= 50 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                    {{ $c }}%
                                </span>
                                @if($item->validated_at)
                                    <span class="badge bg-success"><i class="bi bi-patch-check-fill me-1"></i>Divalidasi</span>
                                @else
                                    <span class="badge bg-secondary"><i class="bi bi-hourglass-split me-1"></i>Belum divalidasi</span>
                                @endif
                                @if($item->bukti_hasil)
                                <a href="{{ $item->bukti_hasil }}" target="_blank" class="badge bg-info text-white text-decoration-none">
                                    <i class="bi bi-link-45deg me-1"></i>Bukti
                                </a>
                                @endif
                                @if($item->catatan_admin)
                                <span class="badge bg-light text-dark border" title="{{ $item->catatan_admin }}">
                                    <i class="bi bi-chat-text text-info me-1"></i>Ada catatan admin
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="d-flex gap-1 flex-shrink-0">
                            <a href="{{ route('pic.laporan-harian.edit', $item) }}" class="btn btn-outline-primary btn-sm" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('pic.laporan-harian.destroy', $item) }}" method="POST"
                                  onsubmit="return confirm('Hapus catatan ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Grafik capaian 30 hari --}}
        @if($chartData->count() > 1)
        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-graph-up me-2 text-primary"></i><strong>Tren Capaian — 30 Hari Terakhir</strong></span>
            </div>
            <div class="card-body py-2">
                <canvas id="chartCapaianPic" height="90"></canvas>
            </div>
        </div>
        @endif

        {{-- Catatan Kinerja PIC Lain --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <span>
                    <i class="bi bi-people-fill me-2 text-primary"></i>
                    <strong>Catatan Kinerja Harian — Semua PIC</strong>
                </span>
                <form method="GET" action="{{ route('pic.laporan-harian.index') }}" class="d-flex align-items-center gap-2">
                    <input type="hidden" name="page" value="1">
                    <label class="small text-muted mb-0">Tanggal:</label>
                    <input type="date" name="team_tanggal" class="form-control form-control-sm" style="width:auto;"
                           value="{{ $teamTanggal }}"
                           onchange="this.form.submit()">
                </form>
            </div>
            <div class="card-body p-0">
                @php
                    $teamTanggalLabel = \Carbon\Carbon::parse($teamTanggal)->locale('id')->translatedFormat('l, d F Y');
                    $isTeamToday = $teamTanggal === $today;
                @endphp
                @if($teamEntries->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                    Tidak ada catatan dari PIC lain untuk
                    <strong>{{ $teamTanggalLabel }}</strong>
                </div>
                @else
                <div class="accordion accordion-flush" id="accordionTeam">
                    @foreach($teamEntries as $teamPicId => $entries)
                    @php
                        $teamPic    = $entries->first()->pic;
                        $teamAvg    = round($entries->avg('capaian_hasil'));
                        $teamValid  = $entries->filter(fn($e) => $e->validated_at)->count();
                        $colorClass = $teamAvg >= 80 ? 'success' : ($teamAvg >= 50 ? 'warning' : 'danger');
                    @endphp
                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-3" type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#teamPic{{ $teamPicId }}">
                                <div class="d-flex align-items-center gap-3 flex-grow-1 me-3">
                                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                         style="width:34px;height:34px;font-size:0.8rem;">
                                        {{ strtoupper(substr($teamPic->name ?? '?', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $teamPic->name ?? 'PIC #'.$teamPicId }}</div>
                                        <div class="small text-muted">{{ $entries->count() }} kegiatan</div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 ms-auto flex-wrap">
                                        <span class="badge bg-{{ $colorClass }}{{ $colorClass === 'warning' ? ' text-dark' : '' }}">
                                            {{ $teamAvg }}%
                                        </span>
                                        @if($teamValid === $entries->count())
                                            <span class="badge bg-success"><i class="bi bi-patch-check-fill me-1"></i>Semua Valid</span>
                                        @elseif($teamValid > 0)
                                            <span class="badge bg-warning text-dark"><i class="bi bi-patch-check me-1"></i>{{ $teamValid }}/{{ $entries->count() }}</span>
                                        @else
                                            <span class="badge bg-secondary"><i class="bi bi-hourglass-split me-1"></i>Belum</span>
                                        @endif
                                    </div>
                                </div>
                            </button>
                        </h2>
                        <div id="teamPic{{ $teamPicId }}" class="accordion-collapse collapse"
                             data-bs-parent="#accordionTeam">
                            <div class="accordion-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead class="table-light" style="font-size:0.8rem;">
                                            <tr>
                                                <th style="width:30px;">#</th>
                                                <th>Judul Kegiatan</th>
                                                <th>Catatan Kerja</th>
                                                <th>Realisasi</th>
                                                <th style="width:80px;" class="text-center">Capaian</th>
                                                <th style="width:80px;" class="text-center">Status</th>
                                                <th style="width:55px;" class="text-center">Bukti</th>
                                            </tr>
                                        </thead>
                                        <tbody style="font-size:0.82rem;">
                                            @foreach($entries as $j => $e)
                                            @php $ec = $e->capaian_hasil; @endphp
                                            <tr>
                                                <td class="text-muted">{{ $j + 1 }}</td>
                                                <td>{{ $e->judul_kegiatan ?: '-' }}</td>
                                                <td style="max-width:200px;">
                                                    <span title="{{ $e->target_kerja }}">{{ \Str::limit($e->target_kerja, 70) }}</span>
                                                </td>
                                                <td style="max-width:200px;">
                                                    <span title="{{ $e->laporan_kinerja }}">{{ \Str::limit($e->laporan_kinerja, 70) }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-{{ $ec >= 80 ? 'success' : ($ec >= 50 ? 'warning text-dark' : 'danger') }}">
                                                        {{ $ec }}%
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    @if($e->validated_at)
                                                        <span class="badge bg-success"><i class="bi bi-patch-check-fill"></i></span>
                                                    @else
                                                        <span class="badge bg-secondary"><i class="bi bi-hourglass-split"></i></span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($e->bukti_hasil)
                                                    <a href="{{ $e->bukti_hasil }}" target="_blank" class="btn btn-outline-info btn-sm py-0">
                                                        <i class="bi bi-link-45deg"></i>
                                                    </a>
                                                    @else
                                                    <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @if(!$isTeamToday)
            <div class="card-footer text-muted small text-center">
                Menampilkan data tanggal {{ $teamTanggalLabel }}
                — <a href="{{ route('pic.laporan-harian.index') }}" class="text-decoration-none">Kembali ke hari ini</a>
            </div>
            @endif
        </div>

        {{-- Riwayat --}}
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-2"></i><strong>Riwayat Catatan</strong></span>
                <span class="badge bg-secondary">{{ $laporan->total() }} total</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:110px">Tanggal</th>
                            <th style="width:180px">Judul Kegiatan</th>
                            <th>Catatan Kerja</th>
                            <th style="width:85px" class="text-center">Capaian</th>
                            <th style="width:100px" class="text-center">Status</th>
                            <th style="width:55px" class="text-center">Bukti</th>
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
                            <td class="small">{{ $item->judul_kegiatan ?: '-' }}</td>
                            <td class="small" style="max-width:220px">
                                <div title="{{ $item->target_kerja }}">{{ Str::limit($item->target_kerja, 80) }}</div>
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
                                    <span class="badge bg-success"><i class="bi bi-patch-check-fill"></i></span>
                                @else
                                    <span class="badge bg-secondary"><i class="bi bi-hourglass-split"></i></span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item->bukti_hasil)
                                <a href="{{ $item->bukti_hasil }}" target="_blank" class="btn btn-outline-info btn-sm">
                                    <i class="bi bi-link-45deg"></i>
                                </a>
                                @else
                                <span class="text-muted">-</span>
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

@section('scripts')
<script>
const range = document.getElementById('capaianRange');
const badge = document.getElementById('capaianBadge');
if (range && badge) {
    range.addEventListener('input', function() {
        badge.textContent = this.value + '%';
        const v = parseInt(this.value);
        badge.className = 'badge ms-1 ' + (v >= 80 ? 'bg-success' : v >= 50 ? 'bg-warning text-dark' : 'bg-danger');
    });
    const iv = parseInt(range.value);
    badge.className = 'badge ms-1 ' + (iv >= 80 ? 'bg-success' : iv >= 50 ? 'bg-warning text-dark' : 'bg-danger');
}
@if($todayEntries->count() === 0 && !session('success') && !session('error') && !$showReminder)
const collapseEl = document.getElementById('formTambah');
if (collapseEl) new bootstrap.Collapse(collapseEl, { show: true });
@endif
</script>

@if($chartData->count() > 1)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    const labels = @json($chartData->pluck('tanggal')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m')));
    const values = @json($chartData->pluck('avg_capaian'));
    const totals = @json($chartData->pluck('total'));
    const ctx    = document.getElementById('chartCapaianPic');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Capaian (%)',
                data: values,
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99,102,241,0.08)',
                tension: 0.3,
                fill: true,
                pointBackgroundColor: '#6366f1',
                pointRadius: 5,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        afterLabel: (ctx) => `${totals[ctx.dataIndex]} kegiatan`
                    }
                }
            },
            scales: {
                y: { min: 0, max: 100, ticks: { callback: v => v + '%' } },
                x: { grid: { display: false } }
            }
        }
    });
})();
</script>
@endif
@endsection
