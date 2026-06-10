@extends('layouts.app')

@section('title', 'Pengaturan Point')
@section('page-title', 'Pengaturan Point PIC & Marketing')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4">
    <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4">
    <i class="bi bi-exclamation-circle-fill me-1"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Initialize Alert --}}
@if($missingPic->isNotEmpty() || $missingMarketing->isNotEmpty())
<div class="alert alert-warning d-flex align-items-center gap-3 mb-4">
    <i class="bi bi-exclamation-triangle-fill fs-4 flex-shrink-0"></i>
    <div class="flex-grow-1">
        <strong>Ada step yang belum dikonfigurasi:</strong>
        @if($missingPic->isNotEmpty())
            PIC — {{ $missingPic->join(', ') }}.
        @endif
        @if($missingMarketing->isNotEmpty())
            Marketing — {{ $missingMarketing->join(', ') }}.
        @endif
        Step ini akan menggunakan fallback <strong>1 pt</strong> sampai diinisialisasi.
    </div>
    <form method="POST" action="{{ route('admin.task-point-settings.init-defaults') }}" class="flex-shrink-0">
        @csrf
        <button type="submit" class="btn btn-warning btn-sm">
            <i class="bi bi-magic"></i> Inisialisasi Default (1 pt)
        </button>
    </form>
</div>
@endif

{{-- ================================================================
     MAIN FORM
     ================================================================ --}}
<form action="{{ route('admin.task-point-settings.update') }}" method="POST" id="settingsForm">
    @csrf
    @method('PUT')

    {{-- === PIC SETTINGS === --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center py-2" style="background:#e0e7ff; border-bottom:2px solid #818cf8;">
            <div>
                <i class="bi bi-person-badge-fill text-primary me-1"></i>
                <strong class="text-primary">Point PIC per Tahap</strong>
            </div>
            <small class="text-muted">Point diberikan saat tahap selesai divalidasi</small>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="picTable">
                <thead style="background:#f1f5f9;">
                    <tr>
                        <th width="48" class="text-center text-muted">#</th>
                        <th width="110">Step</th>
                        <th>Label Tugas <small class="text-muted fw-normal">(klik untuk edit)</small></th>
                        <th width="160" class="text-center">Point per Tugas</th>
                        <th width="80" class="text-center">Aktif</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($picOrder as $stepIdx => $stepKey)
                    @php $setting = $picByKey->get($stepKey); @endphp
                    @if($setting)
                    <tr class="step-row {{ $setting->is_active ? '' : 'inactive-row' }}" data-step="{{ $stepKey }}">
                        <td class="text-center">
                            <span class="badge rounded-pill {{ $setting->is_active ? 'text-bg-primary' : 'text-bg-secondary' }} step-num-badge">
                                {{ $stepIdx + 1 }}
                            </span>
                        </td>
                        <td>
                            <code class="text-primary" style="font-size:.8rem;">{{ $stepKey }}</code>
                        </td>
                        <td>
                            <input type="text"
                                   name="task_label[{{ $setting->id }}]"
                                   value="{{ $setting->task_label }}"
                                   class="form-control form-control-sm inline-label-input"
                                   placeholder="Label tahap..."
                                   maxlength="100">
                        </td>
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <input type="number"
                                       name="points[{{ $setting->id }}]"
                                       value="{{ $setting->points }}"
                                       class="form-control form-control-sm text-center pic-point-input"
                                       data-step="{{ $stepKey }}"
                                       min="0" step="0.01"
                                       style="width:90px;">
                                <small class="text-muted">pt</small>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="form-check form-switch d-flex justify-content-center mb-0">
                                <input type="checkbox"
                                       class="form-check-input pic-active-toggle"
                                       name="is_active[{{ $setting->id }}]"
                                       data-step="{{ $stepKey }}"
                                       value="1"
                                       role="switch"
                                       {{ $setting->is_active ? 'checked' : '' }}>
                            </div>
                        </td>
                    </tr>
                    @else
                    <tr class="table-light step-row-missing">
                        <td class="text-center">
                            <span class="badge rounded-pill text-bg-secondary opacity-50">{{ $stepIdx + 1 }}</span>
                        </td>
                        <td><code class="text-secondary">{{ $stepKey }}</code></td>
                        <td colspan="3" class="text-muted fst-italic" style="font-size:.83rem;">
                            <i class="bi bi-exclamation-circle me-1 text-warning"></i>
                            Belum dikonfigurasi — sistem menggunakan fallback <strong>1 pt</strong>.
                            Klik <strong>Inisialisasi Default</strong> di atas.
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
                <tfoot style="background:#f8fafc;">
                    <tr>
                        <td colspan="3" class="text-end pe-3 fw-semibold text-muted">Total point PIC untuk alur lengkap:</td>
                        <td class="text-center">
                            <strong class="text-primary fs-5" id="picTableTotal">—</strong>
                            <small class="text-muted"> pt</small>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- === MARKETING SETTINGS === --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center py-2" style="background:#dcfce7; border-bottom:2px solid #4ade80;">
            <div>
                <i class="bi bi-megaphone-fill text-success me-1"></i>
                <strong class="text-success">Point Marketing per Submission</strong>
            </div>
            <small class="text-muted">Point diberikan saat submission baru masuk dengan kode Marketing</small>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:#f1f5f9;">
                    <tr>
                        <th width="110">Step</th>
                        <th>Label</th>
                        <th width="160" class="text-center">Point per Submission</th>
                        <th width="80" class="text-center">Aktif</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($marketingSettings as $setting)
                    <tr>
                        <td><code class="text-success" style="font-size:.8rem;">{{ $setting->task_key }}</code></td>
                        <td>
                            <input type="text"
                                   name="task_label[{{ $setting->id }}]"
                                   value="{{ $setting->task_label }}"
                                   class="form-control form-control-sm inline-label-input"
                                   maxlength="100">
                        </td>
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <input type="number"
                                       name="points[{{ $setting->id }}]"
                                       value="{{ $setting->points }}"
                                       class="form-control form-control-sm text-center marketing-point-input"
                                       min="0" step="0.01"
                                       style="width:90px;">
                                <small class="text-muted">pt</small>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="form-check form-switch d-flex justify-content-center mb-0">
                                <input type="checkbox"
                                       class="form-check-input"
                                       name="is_active[{{ $setting->id }}]"
                                       value="1"
                                       role="switch"
                                       {{ $setting->is_active ? 'checked' : '' }}>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-3 fst-italic">
                            Belum ada setting Marketing — klik <strong>Inisialisasi Default</strong> di atas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- === FOOTER === --}}
    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
        <div class="alert alert-info mb-0 py-2 px-3" style="font-size:.82rem; max-width:680px;">
            <i class="bi bi-lightbulb-fill me-1"></i>
            <strong>Perubahan point hanya berlaku untuk transaksi baru ke depan.</strong>
            Data historis tidak berubah otomatis.
            Gunakan tombol <strong>Sync Ulang Poin</strong> di halaman laporan point untuk recalculate:
            <a href="{{ route('admin.pic-points.index') }}" class="alert-link" target="_blank">
                <i class="bi bi-box-arrow-up-right"></i> Laporan Poin PIC
            </a>
            &nbsp;|&nbsp;
            <a href="{{ route('admin.marketing-points.index') }}" class="alert-link" target="_blank">
                <i class="bi bi-box-arrow-up-right"></i> Laporan Poin Marketing
            </a>
        </div>
        <button type="submit" class="btn btn-primary px-5 flex-shrink-0">
            <i class="bi bi-save me-1"></i> Simpan Pengaturan
        </button>
    </div>
</form>

<style>
.inline-label-input {
    border-color: transparent !important;
    background: transparent !important;
    font-weight: 500;
    transition: border-color .15s, background .15s;
}
.inline-label-input:hover {
    border-color: #ced4da !important;
    background: #fff !important;
}
.inline-label-input:focus {
    border-color: #86b7fe !important;
    background: #fff !important;
    box-shadow: 0 0 0 2px rgba(13,110,253,.15) !important;
}
.inactive-row {
    opacity: .65;
    background: #fafafa;
}
.inactive-row .pic-point-input {
    text-decoration: line-through;
    color: #94a3b8;
}
</style>
@endsection

@section('scripts')
<script>
function recalc() {
    let total = 0;
    document.querySelectorAll('.pic-point-input').forEach(input => {
        const row    = input.closest('tr');
        const toggle = row ? row.querySelector('.pic-active-toggle') : null;
        const active = toggle ? toggle.checked : true;
        if (active) total += parseFloat(input.value) || 0;
    });

    const fmtTotal = total % 1 === 0 ? total : total.toFixed(2);
    const el = document.getElementById('picTableTotal');
    if (el) el.textContent = fmtTotal;
}

function syncRowStyle(toggle) {
    const row = toggle.closest('tr');
    if (!row) return;
    row.classList.toggle('inactive-row', !toggle.checked);
    const badge = row.querySelector('.step-num-badge');
    if (badge) badge.className = 'badge rounded-pill step-num-badge ' + (toggle.checked ? 'text-bg-primary' : 'text-bg-secondary');
    recalc();
}

document.querySelectorAll('.pic-point-input').forEach(el => el.addEventListener('input', recalc));
document.querySelectorAll('.pic-active-toggle').forEach(el => el.addEventListener('change', () => syncRowStyle(el)));

recalc();
</script>
@endsection
